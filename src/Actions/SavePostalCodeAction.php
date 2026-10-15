<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Models\AddressAreaPostalCode;
use AIArmada\Addressing\Models\PostalCode;
use AIArmada\Addressing\Support\ModelResolver;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SavePostalCodeAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes, ?PostalCode $record = null): PostalCode
    {
        return DB::transaction(function () use ($attributes, $record): PostalCode {
            $postalCodeClass = ModelResolver::postalCodeClass();
            $postalCode = $record instanceof PostalCode
                ? ($record->exists
                    ? $record::query()->lockForUpdate()->findOrFail($record->getKey())
                    : $record)
                : new $postalCodeClass;
            $countryCode = $postalCode->exists
                ? (string) $postalCode->country_code
                : $this->stringAttribute($attributes, 'country_code', null, uppercase: true);
            $code = $postalCode->exists
                ? (string) $postalCode->code
                : $this->stringAttribute($attributes, 'code', null);

            if (mb_strlen($countryCode) !== 2 || ! ModelResolver::countryClass()::query()->where('iso2', $countryCode)->exists()) {
                throw ValidationException::withMessages([
                    'country_code' => 'The selected country is invalid.',
                ]);
            }

            if (mb_strlen($code) > 20) {
                throw ValidationException::withMessages([
                    'code' => 'The postcode may not be greater than 20 characters.',
                ]);
            }

            $manualCoverage = null;

            if (array_key_exists('manual_coverage', $attributes)) {
                if (! is_array($attributes['manual_coverage'])) {
                    throw ValidationException::withMessages([
                        'manual_coverage' => 'Manual postcode coverage must be a list.',
                    ]);
                }

                $manualCoverage = $this->validateManualCoverage(
                    $attributes['manual_coverage'],
                    $countryCode,
                    $postalCode->exists ? (string) $postalCode->getKey() : null,
                );
            }

            if (array_key_exists('is_active', $attributes) && ! is_bool($attributes['is_active'])) {
                throw ValidationException::withMessages([
                    'is_active' => 'The active status must be a boolean.',
                ]);
            }

            if (array_key_exists('metadata', $attributes) && $attributes['metadata'] !== null && ! is_array($attributes['metadata'])) {
                throw ValidationException::withMessages([
                    'metadata' => 'Postcode metadata must be an array.',
                ]);
            }

            $postalCode->country_code = $countryCode;
            $postalCode->code = $code;

            if (array_key_exists('is_active', $attributes)) {
                $postalCode->is_active = $attributes['is_active'];
            }

            if (array_key_exists('metadata', $attributes)) {
                $postalCode->metadata = $attributes['metadata'];
            }

            if (! $postalCode->save()) {
                throw new DomainException('The postcode could not be saved.');
            }

            if ($manualCoverage !== null) {
                $this->saveManualCoverage($postalCode, $manualCoverage);
            }

            return $postalCode->fresh(['areas']) ?? $postalCode;
        });
    }

    /**
     * @param  array<array-key, mixed>  $coverage
     * @return list<array{area_id: string, relationship_type: string, is_primary: bool}>
     */
    private function validateManualCoverage(array $coverage, string $countryCode, ?string $postalCodeId): array
    {
        $existingManualEdges = $postalCodeId === null
            ? collect()
            : ModelResolver::areaPostalCodeClass()::query()
                ->where('postal_code_id', $postalCodeId)
                ->where('source', 'manual')
                ->whereNull('source_id')
                ->lockForUpdate()
                ->get(['address_area_id', 'relationship_type']);
        $existingKeys = $existingManualEdges->mapWithKeys(
            static fn (AddressAreaPostalCode $edge): array => [self::coverageKey((string) $edge->address_area_id, $edge->relationship_type) => true],
        )->all();

        $normalized = [];
        $areaIds = [];
        $seen = [];

        foreach ($coverage as $item) {
            if (! is_array($item)) {
                throw ValidationException::withMessages([
                    'manual_coverage' => 'Every manual coverage item must be an object.',
                ]);
            }

            $areaId = $item['area_id'] ?? null;
            $relationshipType = $item['relationship_type'] ?? 'served_by';

            if (! is_string($areaId) || mb_trim($areaId) === '' || ! is_string($relationshipType)
                || mb_trim($relationshipType) === '' || mb_strlen(mb_trim($relationshipType)) > 50) {
                throw ValidationException::withMessages([
                    'manual_coverage' => 'Each manual coverage item requires a valid area and relationship type.',
                ]);
            }

            $areaId = mb_trim($areaId);
            $relationshipType = mb_trim($relationshipType);
            $key = self::coverageKey($areaId, $relationshipType);

            if (isset($seen[$key])) {
                throw ValidationException::withMessages([
                    'manual_coverage' => 'Manual coverage cannot contain the same area and relationship type twice.',
                ]);
            }

            $isPrimary = filter_var($item['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($isPrimary === null) {
                throw ValidationException::withMessages([
                    'manual_coverage' => 'The primary flag must be a boolean.',
                ]);
            }

            $seen[$key] = true;
            $areaIds[$areaId] = $areaId;
            $normalized[] = [
                'area_id' => $areaId,
                'relationship_type' => $relationshipType,
                'is_primary' => $isPrimary,
            ];
        }

        if ($areaIds !== []) {
            $areaClass = ModelResolver::areaClass();
            $areaKeyName = (new $areaClass)->getKeyName();
            $areas = $areaClass::query()
                ->whereKey(array_values($areaIds))
                ->where('country_code', $countryCode)
                ->orderBy($areaKeyName)
                ->lockForUpdate()
                ->get()
                ->keyBy(static fn ($area): string => (string) $area->getKey());

            foreach ($normalized as $item) {
                $key = self::coverageKey($item['area_id'], $item['relationship_type']);
                $area = $areas->get($item['area_id']);

                if ($area === null || (! $area->is_active && ! isset($existingKeys[$key]))) {
                    throw ValidationException::withMessages([
                        'manual_coverage' => 'New manual postcode coverage must use an active area in the postcode country.',
                    ]);
                }
            }
        }

        return $normalized;
    }

    /**
     * @param  list<array{area_id: string, relationship_type: string, is_primary: bool}>  $coverage
     */
    private function saveManualCoverage(PostalCode $postalCode, array $coverage): void
    {
        $existing = ModelResolver::areaPostalCodeClass()::query()
            ->where('postal_code_id', $postalCode->getKey())
            ->where('source', 'manual')
            ->whereNull('source_id')
            ->lockForUpdate()
            ->get()
            ->keyBy(static fn (AddressAreaPostalCode $edge): string => self::coverageKey(
                (string) $edge->address_area_id,
                $edge->relationship_type,
            ));
        $retained = [];

        foreach ($coverage as $item) {
            $key = self::coverageKey($item['area_id'], $item['relationship_type']);
            $edge = $existing->get($key);

            if ($edge instanceof AddressAreaPostalCode) {
                $edge->is_primary = $item['is_primary'];

                if (! $edge->save()) {
                    throw new DomainException('Manual postcode coverage could not be saved.');
                }
            } else {
                $pivotClass = ModelResolver::areaPostalCodeClass();
                $edge = new $pivotClass;
                $edge->fill([
                    'postal_code_id' => $postalCode->getKey(),
                    'address_area_id' => $item['area_id'],
                    'source' => 'manual',
                    'relationship_type' => $item['relationship_type'],
                    'is_primary' => $item['is_primary'],
                ]);

                if (! $edge->save()) {
                    throw new DomainException('Manual postcode coverage could not be saved.');
                }
            }

            $retained[] = $key;
        }

        $existing->reject(static fn (AddressAreaPostalCode $edge, string $key): bool => in_array($key, $retained, true))
            ->each(static function (AddressAreaPostalCode $edge): void {
                if (! $edge->delete()) {
                    throw new DomainException('Manual postcode coverage could not be removed.');
                }
            });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function stringAttribute(array $attributes, string $key, ?string $current, bool $uppercase = false): string
    {
        $value = $attributes[$key] ?? $current;

        if (! is_string($value)) {
            throw ValidationException::withMessages([
                $key => 'The ' . str_replace('_', ' ', $key) . ' field is required.',
            ]);
        }

        $value = mb_trim($value);

        if ($uppercase) {
            $value = mb_strtoupper($value);
        }

        if ($value === '') {
            throw ValidationException::withMessages([
                $key => 'The ' . str_replace('_', ' ', $key) . ' field is required.',
            ]);
        }

        return $value;
    }

    private static function coverageKey(string $areaId, string $relationshipType): string
    {
        return $areaId . "\0" . $relationshipType;
    }
}
