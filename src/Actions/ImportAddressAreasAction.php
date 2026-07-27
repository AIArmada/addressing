<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Data\ImportAddressAreaFailureData;
use AIArmada\Addressing\Data\ImportAddressAreasResultData;
use AIArmada\Addressing\Models\AddressArea;
use AIArmada\Addressing\Models\AddressAreaRelationship;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\AddressAreaHierarchy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ImportAddressAreasAction
{
    public function execute(
        AddressAreaSource $source,
        bool $dryRun = false,
        ?string $providerKey = null,
    ): ImportAddressAreasResultData {
        $providerKey = $providerKey !== null ? mb_trim($providerKey) : null;

        if ($providerKey === '') {
            throw new InvalidArgumentException('Address-area provider keys cannot be empty.');
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $failures = [];

        foreach ($source->areas() as $areaData) {
            if ($areaData->source === '' || $areaData->sourceId === '') {
                $failures[] = new ImportAddressAreaFailureData(
                    sourceId: $areaData->sourceId,
                    reason: 'Missing required field: source or sourceId',
                    name: $areaData->name,
                );

                continue;
            }

            if ($areaData->countryCode === '') {
                $failures[] = new ImportAddressAreaFailureData(
                    sourceId: $areaData->sourceId,
                    reason: 'Missing required field: countryCode',
                    name: $areaData->name,
                );

                continue;
            }

            if ($areaData->type === '') {
                $failures[] = new ImportAddressAreaFailureData(
                    sourceId: $areaData->sourceId,
                    reason: 'Missing required field: type',
                    name: $areaData->name,
                );

                continue;
            }

            if ($areaData->name === '') {
                $failures[] = new ImportAddressAreaFailureData(
                    sourceId: $areaData->sourceId,
                    reason: 'Missing required field: name',
                    name: null,
                );

                continue;
            }

            if ($areaData->hierarchyType !== null && mb_trim($areaData->hierarchyType) === '') {
                $failures[] = new ImportAddressAreaFailureData(
                    sourceId: $areaData->sourceId,
                    reason: 'Hierarchy type cannot be empty when supplied',
                    name: $areaData->name,
                );

                continue;
            }

            if ($areaData->hierarchyType !== null && mb_trim($areaData->relationshipType) === '') {
                $failures[] = new ImportAddressAreaFailureData(
                    sourceId: $areaData->sourceId,
                    reason: 'Relationship type cannot be empty when hierarchy type is supplied',
                    name: $areaData->name,
                );

                continue;
            }

            $countryCode = mb_strtoupper(mb_trim($areaData->countryCode));
            $country = AddressCountry::where('iso2', $countryCode)->first();

            if ($country === null) {
                $failures[] = new ImportAddressAreaFailureData(
                    sourceId: $areaData->sourceId,
                    reason: "Country not found for countryCode: {$areaData->countryCode}",
                    name: $areaData->name,
                );

                continue;
            }

            $slug = Str::slug($areaData->name);

            if ($dryRun) {
                $skipped++;

                continue;
            }

            $existing = AddressArea::where('source', $areaData->source)
                ->where('source_id', $areaData->sourceId)
                ->first();

            $parentId = null;
            if ($areaData->parentSourceId !== null && $areaData->parentSourceId !== '') {
                $parent = AddressArea::where('source', $areaData->source)
                    ->where('source_id', $areaData->parentSourceId)
                    ->first();

                if ($parent === null) {
                    $failures[] = new ImportAddressAreaFailureData(
                        sourceId: $areaData->sourceId,
                        reason: "Parent not found for parentSourceId: {$areaData->parentSourceId}",
                        name: $areaData->name,
                    );

                    continue;
                }

                $validationMessage = AddressAreaHierarchy::validateParentAssignment($existing, $parent);

                if ($validationMessage !== null) {
                    $failures[] = new ImportAddressAreaFailureData(
                        sourceId: $areaData->sourceId,
                        reason: $validationMessage,
                        name: $areaData->name,
                    );

                    continue;
                }

                $parentId = $parent->id;
            }

            $metadata = $areaData->metadata;

            if ($providerKey !== null) {
                $metadata['provider'] = $providerKey;
            }

            $data = [
                'country_id' => $country->id,
                'parent_id' => $parentId,
                'country_code' => $countryCode,
                'type' => $areaData->type,
                'level' => $areaData->level,
                'name' => $areaData->name,
                'native_name' => $areaData->nativeName,
                'code' => $areaData->code,
                'slug' => $slug,
                'latitude' => $areaData->latitude,
                'longitude' => $areaData->longitude,
                'source' => $areaData->source,
                'source_id' => $areaData->sourceId,
                'parent_source_id' => $areaData->parentSourceId,
                'is_active' => true,
                'source_payload' => $areaData->sourcePayload !== [] ? $areaData->sourcePayload : null,
                'synced_at' => CarbonImmutable::now(),
                'metadata' => $metadata !== [] ? $metadata : null,
            ];

            if ($existing === null) {
                $existing = AddressArea::create($data);
                $created++;
            } else {
                $existing->fill($data);

                if ($existing->isDirty()) {
                    $existing->save();
                    $updated++;
                } else {
                    $skipped++;
                }
            }

            AddressAreaRelationship::query()
                ->where('child_address_area_id', $existing->getKey())
                ->where('source', $providerKey ?? $areaData->source)
                ->delete();

            if ($areaData->hierarchyType !== null) {
                if ($parentId !== null) {
                    AddressAreaRelationship::query()->updateOrCreate(
                        [
                            'parent_address_area_id' => $parentId,
                            'child_address_area_id' => $existing->getKey(),
                            'relationship_type' => $areaData->relationshipType,
                            'hierarchy_type' => $areaData->hierarchyType,
                            'source' => $providerKey ?? $areaData->source,
                        ],
                        ['metadata' => null],
                    );
                }
            }

        }

        return new ImportAddressAreasResultData(
            created: $created,
            updated: $updated,
            skipped: $skipped,
            failures: $failures,
        );
    }
}
