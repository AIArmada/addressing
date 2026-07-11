<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Data\ImportAddressAreaFailureData;
use AIArmada\Addressing\Data\ImportAddressAreasResultData;
use AIArmada\Addressing\Models\AddressArea;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\AddressAreaHierarchy;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

class ImportAddressAreasAction
{
    public function execute(AddressAreaSource $source, bool $dryRun = false): ImportAddressAreasResultData
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $failures = [];

        // Stage 1: Collect all area data into memory with required-field and country validation
        $staged = [];

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

            $country = AddressCountry::where('iso2', $areaData->countryCode)->first();

            if ($country === null) {
                $failures[] = new ImportAddressAreaFailureData(
                    sourceId: $areaData->sourceId,
                    reason: "Country not found for countryCode: {$areaData->countryCode}",
                    name: $areaData->name,
                );

                continue;
            }

            $staged[$areaData->source . '::' . $areaData->sourceId] = [
                'data' => $areaData,
                'country' => $country,
                'parentKey' => $areaData->parentSourceId !== null && $areaData->parentSourceId !== ''
                    ? $areaData->source . '::' . $areaData->parentSourceId
                    : null,
            ];
        }

        // Collect source-level failures (e.g., CSV column-count mismatches)
        if ($source instanceof CsvAddressAreaSource) {
            foreach ($source->failures() as $csvFailure) {
                $failures[] = new ImportAddressAreaFailureData(
                    sourceId: "csv:row:{$csvFailure['row']}",
                    reason: "CSV column count mismatch: expected {$csvFailure['expected']} columns, got {$csvFailure['actual']}",
                    name: null,
                );
            }
        }

        // Stage 2: Multi-pass processing in dependency order
        $resolved = [];
        $unresolved = array_keys($staged);
        $maxPasses = max(count($staged), 10);

        for ($pass = 0; $pass < $maxPasses && $unresolved !== []; $pass++) {
            $remaining = [];

            foreach ($unresolved as $key) {
                $item = $staged[$key];
                $areaData = $item['data'];
                $country = $item['country'];

                $existing = AddressArea::where('source', $areaData->source)
                    ->where('source_id', $areaData->sourceId)
                    ->first();

                $parent = $item['parentKey'] !== null
                    ? AddressArea::where('source', $areaData->source)
                        ->where('source_id', $areaData->parentSourceId)
                        ->first()
                    : null;

                // Defer if parent is required but not yet available (not in DB and not in-memory resolved)
                if ($item['parentKey'] !== null && $parent === null && ! isset($resolved[$item['parentKey']])) {
                    $remaining[] = $key;

                    continue;
                }

                $parentId = null;

                if ($parent !== null) {
                    if ($parent->country_code !== $areaData->countryCode) {
                        $failures[] = new ImportAddressAreaFailureData(
                            sourceId: $areaData->sourceId,
                            reason: "Parent country mismatch: parent country is '{$parent->country_code}', child country is '{$areaData->countryCode}'",
                            name: $areaData->name,
                        );

                        $resolved[$key] = true;

                        continue;
                    }

                    $validationMessage = AddressAreaHierarchy::validateParentAssignment($existing, $parent);

                    if ($validationMessage !== null) {
                        $failures[] = new ImportAddressAreaFailureData(
                            sourceId: $areaData->sourceId,
                            reason: $validationMessage,
                            name: $areaData->name,
                        );

                        $resolved[$key] = true;

                        continue;
                    }

                    $parentId = $parent->id;
                }

                $slug = Str::slug($areaData->name);

                $data = [
                    'country_id' => $country->id,
                    'parent_id' => $parentId,
                    'country_code' => $areaData->countryCode,
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
                    'source_payload' => $areaData->sourcePayload !== [] ? $areaData->sourcePayload : null,
                    'synced_at' => CarbonImmutable::now(),
                    'metadata' => $areaData->metadata !== [] ? $areaData->metadata : null,
                ];

                if ($dryRun) {
                    if ($existing === null) {
                        $created++;
                    } elseif ($existing->fill($data)->isDirty()) {
                        $updated++;
                    } else {
                        $skipped++;
                    }

                    $resolved[$key] = true;

                    continue;
                }

                if ($existing === null) {
                    AddressArea::create($data);
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

                $resolved[$key] = true;
            }

            $unresolved = $remaining;
        }

        // Items still unresolved after max passes become failures
        foreach ($unresolved as $key) {
            $item = $staged[$key];

            $failures[] = new ImportAddressAreaFailureData(
                sourceId: $item['data']->sourceId,
                reason: "Parent not found for parentSourceId: {$item['data']->parentSourceId}",
                name: $item['data']->name,
            );
        }

        return new ImportAddressAreasResultData(
            created: $created,
            updated: $updated,
            skipped: $skipped,
            failures: $failures,
        );
    }
}
