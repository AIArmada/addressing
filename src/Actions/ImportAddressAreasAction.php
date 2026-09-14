<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Data\AddressAreaData;
use AIArmada\Addressing\Data\ImportAddressAreaFailureData;
use AIArmada\Addressing\Data\ImportAddressAreasResultData;
use AIArmada\Addressing\Models\AddressArea;
use AIArmada\Addressing\Models\AddressAreaRelationship;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\AddressAreaHierarchy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ImportAddressAreasAction
{
    public function execute(
        AddressAreaSource $source,
        bool $dryRun = false,
        ?string $providerKey = null,
        bool $reactivate = false,
    ): ImportAddressAreasResultData {
        $providerKey = $providerKey !== null ? mb_trim($providerKey) : null;

        if ($providerKey === '') {
            throw new InvalidArgumentException('Address-area provider keys cannot be empty.');
        }

        return DB::transaction(function () use ($source, $dryRun, $providerKey, $reactivate): ImportAddressAreasResultData {
            $created = 0;
            $updated = 0;
            $skipped = 0;
            $failures = [];

            $countryIds = AddressCountry::query()->pluck('id', 'iso2')->all();
            $areasByKey = [];

            foreach ($source->areas() as $areaData) {
                $failure = $this->validateFields($areaData);

                if ($failure !== null) {
                    $failures[] = $failure;

                    continue;
                }

                $countryCode = mb_strtoupper(mb_trim($areaData->countryCode));
                $countryId = $countryIds[$countryCode] ?? null;

                if ($countryId === null) {
                    $failures[] = new ImportAddressAreaFailureData(
                        sourceId: $areaData->sourceId,
                        reason: "Country not found for countryCode: {$areaData->countryCode}",
                        name: $areaData->name,
                    );

                    continue;
                }

                $existing = $this->findArea($areasByKey, $areaData->source, $areaData->sourceId);
                $parent = $this->resolveParent($areasByKey, $areaData, $existing, $failures);

                if ($parent === false) {
                    continue;
                }

                if ($dryRun) {
                    $skipped++;

                    continue;
                }

                $result = $this->persistRow($areaData, $countryId, $countryCode, $existing, $parent, $providerKey, $reactivate, $areasByKey);

                match ($result) {
                    'created' => $created++,
                    'updated' => $updated++,
                    default => $skipped++,
                };
            }

            return new ImportAddressAreasResultData(
                created: $created,
                updated: $updated,
                skipped: $skipped,
                failures: $failures,
            );
        });
    }

    private function validateFields(AddressAreaData $areaData): ?ImportAddressAreaFailureData
    {
        if ($areaData->source === '' || $areaData->sourceId === '') {
            return new ImportAddressAreaFailureData(
                sourceId: $areaData->sourceId,
                reason: 'Missing required field: source or sourceId',
                name: $areaData->name,
            );
        }

        if ($areaData->countryCode === '') {
            return new ImportAddressAreaFailureData(
                sourceId: $areaData->sourceId,
                reason: 'Missing required field: countryCode',
                name: $areaData->name,
            );
        }

        if ($areaData->type === '') {
            return new ImportAddressAreaFailureData(
                sourceId: $areaData->sourceId,
                reason: 'Missing required field: type',
                name: $areaData->name,
            );
        }

        if ($areaData->name === '') {
            return new ImportAddressAreaFailureData(
                sourceId: $areaData->sourceId,
                reason: 'Missing required field: name',
                name: null,
            );
        }

        if ($areaData->hierarchyType !== null && mb_trim($areaData->hierarchyType) === '') {
            return new ImportAddressAreaFailureData(
                sourceId: $areaData->sourceId,
                reason: 'Hierarchy type cannot be empty when supplied',
                name: $areaData->name,
            );
        }

        if ($areaData->hierarchyType !== null && mb_trim($areaData->relationshipType) === '') {
            return new ImportAddressAreaFailureData(
                sourceId: $areaData->sourceId,
                reason: 'Relationship type cannot be empty when hierarchy type is supplied',
                name: $areaData->name,
            );
        }

        return null;
    }

    /**
     * @param  array<string, AddressArea|null>  $areasByKey
     * @param  list<ImportAddressAreaFailureData>  $failures
     * @return AddressArea|null|false Parent area, null when rowless, or false on failure.
     */
    private function resolveParent(
        array &$areasByKey,
        AddressAreaData $areaData,
        ?AddressArea $existing,
        array &$failures,
    ): AddressArea | null | false {
        if ($areaData->parentSourceId === null || $areaData->parentSourceId === '') {
            return null;
        }

        $parent = $this->findArea($areasByKey, $areaData->source, $areaData->parentSourceId);

        if ($parent === null) {
            $failures[] = new ImportAddressAreaFailureData(
                sourceId: $areaData->sourceId,
                reason: "Parent not found for parentSourceId: {$areaData->parentSourceId}",
                name: $areaData->name,
            );

            return false;
        }

        $validationMessage = AddressAreaHierarchy::validateParentAssignment($existing, $parent);

        if ($validationMessage !== null) {
            $failures[] = new ImportAddressAreaFailureData(
                sourceId: $areaData->sourceId,
                reason: $validationMessage,
                name: $areaData->name,
            );

            return false;
        }

        return $parent;
    }

    /**
     * @param  array<string, AddressArea|null>  $areasByKey
     */
    private function findArea(array &$areasByKey, string $source, string $sourceId): ?AddressArea
    {
        $key = $source . "\0" . $sourceId;

        if (! array_key_exists($key, $areasByKey)) {
            $areasByKey[$key] = AddressArea::where('source', $source)
                ->where('source_id', $sourceId)
                ->first();
        }

        return $areasByKey[$key];
    }

    /**
     * @param  array<string, AddressArea|null>  $areasByKey
     * @return 'created'|'updated'|'skipped'
     */
    private function persistRow(
        AddressAreaData $areaData,
        string $countryId,
        string $countryCode,
        ?AddressArea $existing,
        ?AddressArea $parent,
        ?string $providerKey,
        bool $reactivate,
        array &$areasByKey,
    ): string {
        $metadata = $areaData->metadata;

        if ($providerKey !== null) {
            $metadata['provider'] = $providerKey;
        }

        $data = [
            'country_id' => $countryId,
            'parent_id' => $parent?->getKey(),
            'country_code' => $countryCode,
            'type' => $areaData->type,
            'level' => $areaData->level,
            'name' => $areaData->name,
            'native_name' => $areaData->nativeName,
            'code' => $areaData->code,
            'slug' => Str::slug($areaData->name),
            'latitude' => $areaData->latitude,
            'longitude' => $areaData->longitude,
            'source' => $areaData->source,
            'source_id' => $areaData->sourceId,
            'parent_source_id' => $areaData->parentSourceId,
            'source_payload' => $areaData->sourcePayload !== [] ? $areaData->sourcePayload : null,
            'metadata' => $metadata !== [] ? $metadata : null,
        ];

        if ($existing === null) {
            $existing = AddressArea::create([
                ...$data,
                'is_active' => true,
                'synced_at' => CarbonImmutable::now(),
            ]);
            $areasByKey[$areaData->source . "\0" . $areaData->sourceId] = $existing;
            $outcome = 'created';
        } else {
            $existing->fill($data);

            if ($reactivate) {
                $existing->is_active = true;
            }

            if ($existing->isDirty()) {
                $existing->synced_at = CarbonImmutable::now();
                $existing->save();
                $outcome = 'updated';
            } else {
                $outcome = 'skipped';
            }
        }

        AddressAreaRelationship::query()
            ->where('child_address_area_id', $existing->getKey())
            ->where('source', $providerKey ?? $areaData->source)
            ->delete();

        if ($areaData->hierarchyType !== null && $parent !== null) {
            AddressAreaRelationship::query()->updateOrCreate(
                [
                    'parent_address_area_id' => $parent->getKey(),
                    'child_address_area_id' => $existing->getKey(),
                    'relationship_type' => $areaData->relationshipType,
                    'hierarchy_type' => $areaData->hierarchyType,
                    'source' => $providerKey ?? $areaData->source,
                ],
                ['metadata' => null],
            );
        }

        return $outcome;
    }
}
