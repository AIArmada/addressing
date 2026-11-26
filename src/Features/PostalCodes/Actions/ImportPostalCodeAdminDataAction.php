<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\PostalCodes\Actions;

use AIArmada\Addressing\Contracts\PostalCodeAdminImporter;
use AIArmada\Addressing\Features\PostalCodes\Models\AddressAreaPostalCode;
use AIArmada\Addressing\Features\PostalCodes\Models\PostalCode;
use AIArmada\Addressing\Support\ModelResolver;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

final readonly class ImportPostalCodeAdminDataAction implements PostalCodeAdminImporter
{
    /**
     * Import one postcode row and its explicitly supplied source-owned coverage.
     * An empty coverage list leaves existing coverage unchanged.
     *
     * @param  array<string, mixed>|null  $metadata
     * @param  list<array<string, mixed>>  $coverage
     */
    public function execute(
        string $countryCode,
        string $code,
        bool $isActive,
        ?array $metadata,
        array $coverage,
    ): string {
        $countryCode = mb_strtoupper(mb_trim($countryCode));
        $code = mb_trim($code);

        if (mb_strlen($countryCode) !== 2) {
            throw ValidationException::withMessages([
                'country_code' => 'The selected country is invalid.',
            ]);
        }

        if ($code === '' || mb_strlen($code) > 20) {
            throw ValidationException::withMessages([
                'code' => 'The postcode must contain between 1 and 20 characters.',
            ]);
        }

        return ModelResolver::postalCodeClass()::query()->getConnection()->transaction(function () use ($code, $countryCode, $isActive, $metadata, $coverage): string {
            if (ModelResolver::countryClass()::query()->where('iso2', $countryCode)->lockForUpdate()->first() === null) {
                throw ValidationException::withMessages([
                    'country_code' => 'The selected country is invalid.',
                ]);
            }

            $postalCodeClass = ModelResolver::postalCodeClass();
            $postalCode = $postalCodeClass::query()
                ->where('country_code', $countryCode)
                ->where('code', $code)
                ->lockForUpdate()
                ->first() ?? new $postalCodeClass;

            $normalizedCoverage = $this->normalizeCoverage($coverage);
            $validatedCoverage = $this->resolveCoverage($countryCode, $normalizedCoverage);
            $existingLinks = $this->lockSourceCoverage($postalCode, $normalizedCoverage);
            $replacementScopes = $this->replacementScopes($normalizedCoverage);
            $this->assertNoRetainedConflicts($validatedCoverage, $existingLinks, $replacementScopes);
            $postalCode->country_code = $countryCode;
            $postalCode->code = $code;
            $postalCode->is_active = $isActive;
            $postalCode->metadata = $metadata;

            if (! $postalCode->save()) {
                throw new DomainException('The postcode could not be imported.');
            }

            if ($validatedCoverage !== []) {
                foreach ($existingLinks as $existingLink) {
                    if (! isset($replacementScopes[self::sourceScopeKey((string) $existingLink->source, $existingLink->source_id)])) {
                        continue;
                    }

                    if (! $existingLink->delete()) {
                        throw new DomainException('Existing postcode coverage could not be replaced.');
                    }
                }

                foreach ($validatedCoverage as $item) {
                    $pivotClass = ModelResolver::areaPostalCodeClass();
                    $link = new $pivotClass;
                    $link->fill([
                        'postal_code_id' => $postalCode->getKey(),
                        'address_area_id' => $item['address_area_id'],
                        'source' => $item['source'],
                        'source_id' => $item['source_id'],
                        'relationship_type' => $item['relationship_type'],
                        'is_primary' => $item['is_primary'],
                    ]);

                    if (! $link->save()) {
                        throw new DomainException('Postcode coverage could not be imported.');
                    }
                }
            }

            return (string) $postalCode->getKey();
        });
    }

    /**
     * Validate and normalize the incoming link values before querying related rows.
     *
     * @param  list<array<string, mixed>>  $coverage
     * @return list<array{index: int, source: string, source_id: string|null, area_source: string, area_source_id: string, relationship_type: string, is_primary: bool}>
     */
    private function normalizeCoverage(array $coverage): array
    {
        $validated = [];

        foreach ($coverage as $index => $item) {
            if (! is_array($item)) {
                throw ValidationException::withMessages([
                    "coverage.{$index}" => 'The postcode coverage entry must be an object.',
                ]);
            }

            $source = $item['source'] ?? null;
            $sourceId = $item['source_id'] ?? null;
            $areaSource = $item['area_source'] ?? null;
            $areaSourceId = $item['area_source_id'] ?? null;
            $relationshipType = $item['relationship_type'] ?? 'served_by';
            $isPrimary = $item['is_primary'] ?? false;

            if (! is_string($source) || mb_trim($source) === '' || mb_strlen(mb_trim($source)) > 100
                || ($sourceId !== null && (! is_string($sourceId) || mb_strlen(mb_trim($sourceId)) > 255))
                || ! is_string($areaSource) || mb_trim($areaSource) === '' || mb_strlen(mb_trim($areaSource)) > 100
                || ! is_string($areaSourceId) || mb_trim($areaSourceId) === '' || mb_strlen(mb_trim($areaSourceId)) > 255
                || ! is_string($relationshipType) || mb_trim($relationshipType) === '' || mb_strlen(mb_trim($relationshipType)) > 50
                || ! is_bool($isPrimary)) {
                throw ValidationException::withMessages([
                    "coverage.{$index}" => 'The postcode coverage entry is invalid.',
                ]);
            }

            $source = mb_trim($source);
            $sourceId = $sourceId !== null ? mb_trim($sourceId) : null;
            $areaSource = mb_trim($areaSource);
            $areaSourceId = mb_trim($areaSourceId);
            $relationshipType = mb_trim($relationshipType);
            $validated[] = [
                'index' => $index,
                'source' => $source,
                'source_id' => $sourceId,
                'area_source' => $areaSource,
                'area_source_id' => $areaSourceId,
                'relationship_type' => $relationshipType,
                'is_primary' => $isPrimary,
            ];
        }

        return $validated;
    }

    /**
     * Lock every existing edge owned by the incoming sources so the real
     * unique identity can be checked before any replacement.
     *
     * @param  list<array{index: int, source: string, source_id: string|null, area_source: string, area_source_id: string, relationship_type: string, is_primary: bool}>  $coverage
     * @return Collection<int, AddressAreaPostalCode>
     */
    private function lockSourceCoverage(PostalCode $postalCode, array $coverage): Collection
    {
        if ($coverage === []) {
            return new Collection;
        }

        $sources = collect($coverage)
            ->map(static fn (array $item): string => $item['source'])
            ->unique()
            ->sort()
            ->values()
            ->all();
        $pivotClass = ModelResolver::areaPostalCodeClass();

        return $pivotClass::query()
            ->where('postal_code_id', $postalCode->getKey())
            ->whereIn('source', $sources)
            ->orderBy((new $pivotClass)->getKeyName())
            ->lockForUpdate()
            ->get(['id', 'address_area_id', 'source', 'source_id', 'relationship_type']);
    }

    /**
     * @param  list<array{index: int, source: string, source_id: string|null, area_source: string, area_source_id: string, relationship_type: string, is_primary: bool}>  $coverage
     * @return array<string, true>
     */
    private function replacementScopes(array $coverage): array
    {
        $scopes = [];

        foreach ($coverage as $item) {
            $scopes[self::sourceScopeKey($item['source'], $item['source_id'])] = true;
        }

        return $scopes;
    }

    /**
     * @param  list<array{index: int, source: string, source_id: string|null, address_area_id: string, relationship_type: string, is_primary: bool}>  $coverage
     * @param  Collection<int, AddressAreaPostalCode>  $existingLinks
     * @param  array<string, true>  $replacementScopes
     */
    private function assertNoRetainedConflicts(array $coverage, Collection $existingLinks, array $replacementScopes): void
    {
        $existingByUniqueKey = [];

        foreach ($existingLinks as $existingLink) {
            $existingByUniqueKey[self::coverageUniqueKey(
                (string) $existingLink->address_area_id,
                (string) $existingLink->relationship_type,
                (string) $existingLink->source,
            )] = $existingLink;
        }

        foreach ($coverage as $item) {
            $existing = $existingByUniqueKey[self::coverageUniqueKey(
                $item['address_area_id'],
                $item['relationship_type'],
                $item['source'],
            )] ?? null;

            if (! $existing instanceof AddressAreaPostalCode) {
                continue;
            }

            if (isset($replacementScopes[self::sourceScopeKey((string) $existing->source, $existing->source_id)])) {
                continue;
            }

            throw ValidationException::withMessages([
                "coverage.{$item['index']}.source_id" => sprintf(
                    'The postcode coverage area is already owned by source "%s" with source ID "%s".',
                    (string) $existing->source,
                    (string) ($existing->source_id ?? ''),
                ),
            ]);
        }
    }

    /**
     * Resolve and lock every referenced area in primary-key order before returning validated links.
     *
     * @param  list<array{index: int, source: string, source_id: string|null, area_source: string, area_source_id: string, relationship_type: string, is_primary: bool}>  $coverage
     * @return list<array{index: int, source: string, source_id: string|null, address_area_id: string, relationship_type: string, is_primary: bool}>
     */
    private function resolveCoverage(string $countryCode, array $coverage): array
    {
        if ($coverage === []) {
            return [];
        }

        $areaScopes = collect($coverage)
            ->map(static fn (array $item): array => [$item['area_source'], $item['area_source_id']])
            ->unique(static fn (array $scope): string => $scope[0] . "\0" . $scope[1])
            ->sortBy(static fn (array $scope): string => $scope[0] . "\0" . $scope[1])
            ->values();
        $areaClass = ModelResolver::areaClass();
        $areaKeyName = (new $areaClass)->getKeyName();
        $areas = $areaClass::query()
            ->where('country_code', $countryCode)
            ->where(static function ($query) use ($areaScopes): void {
                foreach ($areaScopes as [$source, $sourceId]) {
                    $query->orWhere(static function ($query) use ($source, $sourceId): void {
                        $query->where('source', $source)->where('source_id', $sourceId);
                    });
                }
            })
            ->orderBy($areaKeyName)
            ->lockForUpdate()
            ->get()
            ->keyBy(static fn ($area): string => $area->source . "\0" . $area->source_id);
        $validated = [];
        $keys = [];

        foreach ($coverage as $item) {
            $area = $areas->get($item['area_source'] . "\0" . $item['area_source_id']);

            if ($area === null) {
                throw ValidationException::withMessages([
                    "coverage.{$item['index']}.area_source_id" => 'The postcode coverage area does not exist in the selected country.',
                ]);
            }

            $key = implode("\0", [
                (string) $area->getKey(),
                $item['relationship_type'],
                $item['source'],
            ]);

            if (isset($keys[$key])) {
                throw ValidationException::withMessages([
                    "coverage.{$item['index']}" => 'The postcode coverage contains a duplicate source relationship.',
                ]);
            }

            $keys[$key] = true;
            $validated[] = [
                'index' => $item['index'],
                'source' => $item['source'],
                'source_id' => $item['source_id'],
                'address_area_id' => (string) $area->getKey(),
                'relationship_type' => $item['relationship_type'],
                'is_primary' => $item['is_primary'],
            ];
        }

        return $validated;
    }

    private static function sourceScopeKey(string $source, ?string $sourceId): string
    {
        return mb_strlen($source) . ':' . $source . ($sourceId === null ? 'N' : 'S' . mb_strlen($sourceId) . ':' . $sourceId);
    }

    private static function coverageUniqueKey(string $areaId, string $relationshipType, string $source): string
    {
        return $areaId . "\0" . $relationshipType . "\0" . $source;
    }
}
