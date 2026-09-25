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
        ?callable $progress = null,
    ): ImportAddressAreasResultData {
        $providerKey = $providerKey !== null ? mb_trim($providerKey) : null;

        if ($providerKey === '') {
            throw new InvalidArgumentException('Address-area provider keys cannot be empty.');
        }

        return DB::transaction(function () use ($source, $dryRun, $providerKey, $reactivate, $progress): ImportAddressAreasResultData {
            $created = 0;
            $updated = 0;
            $skipped = 0;
            $failures = [];

            $countryIds = AddressCountry::query()->pluck('id', 'iso2')->all();
            $now = CarbonImmutable::now();

            $rows = $source->areas()->all();
            $total = count($rows);
            $done = 0;

            // One preload replaces a SELECT per row. Rows staged by this run
            // join the map as they are prepped, so file order keeps its
            // meaning: a parent later in the file is still "not found".
            $areasByKey = $this->preloadAreas($rows);

            $inserts = [];
            $staged = [];
            $linkStates = [];

            foreach ($rows as $areaData) {
                $done++;

                if ($progress !== null) {
                    $progress('areas', $done, $total);
                }

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

                $key = $areaData->source . "\0" . $areaData->sourceId;
                $existing = $areasByKey[$key] ?? null;
                $parent = null;
                $parentId = null;

                if ($areaData->parentSourceId !== null && $areaData->parentSourceId !== '') {
                    $parent = $areasByKey[$areaData->source . "\0" . $areaData->parentSourceId] ?? null;

                    if ($parent === null) {
                        $failures[] = new ImportAddressAreaFailureData(
                            sourceId: $areaData->sourceId,
                            reason: "Parent not found for parentSourceId: {$areaData->parentSourceId}",
                            name: $areaData->name,
                        );

                        continue;
                    }

                    // New rows skip hierarchy validation: validating a null
                    // record always passes. Note the cycle walk below queries
                    // stored rows, so it cannot see rows staged by this run;
                    // a cycle routed through staged rows is only reported
                    // once every link exists in the database.
                    if ($existing !== null) {
                        $validationMessage = AddressAreaHierarchy::validateParentAssignment(
                            $this->hydrateArea($existing),
                            $this->hydrateArea($parent),
                        );

                        if ($validationMessage !== null) {
                            $failures[] = new ImportAddressAreaFailureData(
                                sourceId: $areaData->sourceId,
                                reason: $validationMessage,
                                name: $areaData->name,
                            );

                            continue;
                        }
                    }

                    $parentId = $parent['id'];
                }

                if ($dryRun) {
                    $skipped++;

                    continue;
                }

                $result = $this->persistPrepared(
                    $areaData,
                    $key,
                    $countryId,
                    $countryCode,
                    $parentId,
                    $existing,
                    $providerKey,
                    $reactivate,
                    $now,
                    $areasByKey,
                    $inserts,
                    $staged,
                );

                $this->trackLink($linkStates, $areaData, (string) $areasByKey[$key]['id'], $parentId, $providerKey);

                match ($result) {
                    'created' => $created++,
                    'updated' => $updated++,
                    default => $skipped++,
                };
            }

            $this->flushInserts($inserts);
            $this->syncLinks($linkStates, $now);

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
     * Preload stored rows for every source in the payload, keyed for lookup.
     *
     * @param  list<AddressAreaData>  $rows
     * @return array<string, array<string, mixed>>
     */
    private function preloadAreas(array $rows): array
    {
        $sources = [];

        foreach ($rows as $areaData) {
            $sources[$areaData->source] = true;
        }

        $areasByKey = [];

        if ($sources === []) {
            return $areasByKey;
        }

        $stored = AddressArea::query()->toBase()
            ->whereIn('source', array_keys($sources))
            ->get();

        foreach ($stored as $row) {
            $attributes = (array) $row;
            $areasByKey[$attributes['source'] . "\0" . $attributes['source_id']] = $attributes;
        }

        return $areasByKey;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function hydrateArea(array $attributes): AddressArea
    {
        $area = new AddressArea;
        $area->setRawAttributes($attributes, true);
        $area->exists = true;

        return $area;
    }

    /**
     * @param  array<string, mixed>|null  $existing
     * @param  array<string, array<string, mixed>>  $areasByKey
     * @param  array<string, array<string, mixed>>  $inserts
     * @param  array<string, true>  $staged
     * @return 'created'|'updated'|'skipped'
     */
    private function persistPrepared(
        AddressAreaData $areaData,
        string $key,
        string $countryId,
        string $countryCode,
        ?string $parentId,
        ?array $existing,
        ?string $providerKey,
        bool $reactivate,
        CarbonImmutable $now,
        array &$areasByKey,
        array &$inserts,
        array &$staged,
    ): string {
        $metadata = $areaData->metadata;

        if ($providerKey !== null) {
            $metadata['provider'] = $providerKey;
        }

        $data = [
            'country_id' => $countryId,
            'parent_id' => $parentId,
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
            $payload = [
                ...$data,
                'id' => (string) Str::uuid7(),
                'is_active' => true,
                'synced_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $payload['source_payload'] = $this->encodeJson($payload['source_payload']);
            $payload['metadata'] = $this->encodeJson($payload['metadata']);

            $inserts[$key] = $payload;
            $areasByKey[$key] = $payload;
            $staged[$key] = true;

            return 'created';
        }

        $model = $this->hydrateArea($existing);
        $model->fill($data);

        if ($reactivate) {
            $model->is_active = true;
        }

        if (! $model->isDirty()) {
            return 'skipped';
        }

        $model->synced_at = $now;

        if (isset($staged[$key])) {
            // A repeated source id later in the file updates the staged row;
            // it is not in the database yet, so there is nothing to save.
            $attributes = $model->getAttributes();
            $attributes['synced_at'] = $now;
            $inserts[$key] = array_merge($inserts[$key], $attributes);
            $areasByKey[$key] = $inserts[$key];

            return 'updated';
        }

        $model->save();
        $areasByKey[$key] = $model->getAttributes();

        return 'updated';
    }

    private function encodeJson(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    /**
     * Remember the link outcome for one processed row. The last occurrence
     * of a child/source pair wins, matching sequential delete-and-recreate.
     *
     * @param  array<string, array<string, mixed>|null>  $linkStates
     */
    private function trackLink(
        array &$linkStates,
        AddressAreaData $areaData,
        string $childId,
        ?string $parentId,
        ?string $providerKey,
    ): void {
        $relSource = $providerKey ?? $areaData->source;

        if ($areaData->hierarchyType !== null && $parentId !== null) {
            $linkStates[$childId . "\0" . $relSource] = [
                'parent_address_area_id' => $parentId,
                'child_address_area_id' => $childId,
                'relationship_type' => $areaData->relationshipType,
                'hierarchy_type' => $areaData->hierarchyType,
                'source' => $relSource,
            ];

            return;
        }

        $linkStates[$childId . "\0" . $relSource] = null;
    }

    /**
     * @param  array<string, array<string, mixed>>  $inserts
     */
    private function flushInserts(array $inserts): void
    {
        if ($inserts === []) {
            return;
        }

        foreach (array_chunk(array_values($inserts), 500) as $chunk) {
            AddressArea::query()->insert($chunk);
        }
    }

    /**
     * Replace stale source-owned links with the tracked outcomes.
     *
     * @param  array<string, array<string, mixed>|null>  $linkStates
     */
    private function syncLinks(array $linkStates, CarbonImmutable $now): void
    {
        if ($linkStates === []) {
            return;
        }

        $childrenBySource = [];
        $creates = [];

        foreach ($linkStates as $composite => $link) {
            [$childId, $relSource] = explode("\0", $composite, 2);
            $childrenBySource[$relSource][] = $childId;

            if ($link !== null) {
                $creates[] = [
                    ...$link,
                    'id' => (string) Str::uuid7(),
                    'metadata' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach ($childrenBySource as $relSource => $childIds) {
            foreach (array_chunk(array_values(array_unique($childIds)), 500) as $chunk) {
                AddressAreaRelationship::query()
                    ->where('source', $relSource)
                    ->whereIn('child_address_area_id', $chunk)
                    ->delete();
            }
        }

        foreach (array_chunk($creates, 500) as $chunk) {
            AddressAreaRelationship::query()->insert($chunk);
        }
    }
}
