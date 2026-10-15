<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Models\AddressArea;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\AddressingTableResolver;
use AIArmada\Addressing\Support\ModelResolver;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class SeedCountryGeographiesAction
{
    public function __construct(
        private readonly Container $container,
        private readonly ImportAddressAreasAction $importAddressAreas,
    ) {}

    /**
     * @param  ?callable(string, int, int, ?string=): void  $progress
     * @return array{seeded: list<string>, skipped: list<string>, areas: array}
     */
    public function execute(?string $countryCode = null, ?callable $progress = null): array
    {
        $seeded = [];
        $skipped = [];
        $areas = [];
        $requestedCode = $countryCode !== null ? mb_strtoupper(mb_trim($countryCode)) : null;

        foreach (config('addressing.geography.providers', []) as $providerClass) {
            if (! is_string($providerClass)) {
                throw new InvalidArgumentException('Addressing geography providers must be class strings.');
            }

            $provider = $this->container->make($providerClass);

            if (! $provider instanceof CountryGeographyProvider) {
                throw new InvalidArgumentException(sprintf(
                    '%s must implement %s.',
                    $providerClass,
                    CountryGeographyProvider::class,
                ));
            }

            $providerCode = mb_strtoupper(mb_trim($provider->countryCode()));

            if ($requestedCode !== null && $requestedCode !== $providerCode) {
                $skipped[] = $providerCode;

                continue;
            }

            $country = ModelResolver::countryClass()::query()->where('iso2', $providerCode)->first();

            if (! $country instanceof AddressCountry) {
                throw new InvalidArgumentException(sprintf(
                    'Cannot seed geography for %s because the country has not been seeded.',
                    $providerCode,
                ));
            }

            $report = $progress === null
                ? null
                : function (string $phase, int $done, int $total) use ($progress, $providerCode): void {
                    $progress($phase, $done, $total, "{$providerCode} {$phase}");
                };

            $areaSummary = $this->connection()->transaction(function () use ($provider, $country, $providerCode, $report): ?array {
                $provider->seed($country);

                if (! $provider instanceof CountryHierarchyProvider) {
                    return null;
                }

                $providerKey = mb_trim($provider->providerKey());

                if ($providerKey === '') {
                    throw new InvalidArgumentException('Addressing geography provider keys cannot be empty.');
                }

                $areaSource = $provider->addressAreaSource();
                $areaResult = $this->importAddressAreas->execute($areaSource, providerKey: $providerKey, reactivate: true, progress: $report);

                if ($areaResult->hasFailures()) {
                    throw new InvalidArgumentException(sprintf(
                        'Cannot seed %s address hierarchy because %d area rows failed.',
                        $providerCode,
                        count($areaResult->failures),
                    ));
                }

                $this->linkStateAreas($country, $provider->stateAreaMappings(), $providerKey, $report);

                if ($provider instanceof CountryAddressAreaMetadataProvider) {
                    $this->syncAreaMetadata($country, $provider, $providerKey, $report);
                }

                return [
                    'created' => $areaResult->created,
                    'updated' => $areaResult->updated,
                    'skipped' => $areaResult->skipped,
                ];
            });

            if ($areaSummary !== null) {
                $areas[$providerCode] = $areaSummary;
            }

            $seeded[] = $providerCode;
        }

        return [
            'seeded' => array_values(array_unique($seeded)),
            'skipped' => array_values(array_unique($skipped)),
            'areas' => $areas,
        ];
    }

    private function syncAreaMetadata(AddressCountry $country, CountryAddressAreaMetadataProvider $provider, string $providerKey, ?callable $progress = null): void
    {
        $this->connection()->transaction(function () use ($country, $provider, $providerKey, $progress): void {
            $providerAreas = ModelResolver::areaClass()::query()
                ->where('country_id', $country->getKey())
                ->where('metadata->provider', $providerKey)
                ->lockForUpdate()
                ->get();
            $areas = $providerAreas
                ->where('is_active', true)
                ->keyBy('source_id');

            $rolesBySource = $provider->areaRoles($country);
            $rolesTotal = count($rolesBySource);
            $rolesDone = 0;

            $roles = [];
            foreach ($rolesBySource as $sourceId => $sourceRoles) {
                $rolesDone++;

                if ($progress !== null) {
                    $progress('roles', $rolesDone, $rolesTotal);
                }

                $area = $areas->get($sourceId);

                if (! $area instanceof AddressArea) {
                    continue;
                }

                foreach ($sourceRoles as $role) {
                    $roles[] = [
                        'address_area_id' => $area->getKey(),
                        'role' => $role['role'],
                        'source' => $providerKey,
                        'country_code' => $role['country_code'] ?? $country->iso2,
                        'is_primary' => $role['is_primary'] ?? false,
                    ];
                }
            }
            $this->syncSourceRows(AddressingTableResolver::resolve('area_roles'), $providerKey, $roles, ['address_area_id', 'role', 'country_code'], ['is_primary'], (string) $country->getKey(), $providerKey, 'address_area_id');

            $namesBySource = $provider->areaNames($country);
            $namesTotal = count($namesBySource);
            $namesDone = 0;

            $names = [];
            foreach ($namesBySource as $sourceId => $sourceNames) {
                $namesDone++;

                if ($progress !== null) {
                    $progress('names', $namesDone, $namesTotal);
                }

                $area = $areas->get($sourceId);

                if (! $area instanceof AddressArea) {
                    continue;
                }

                foreach ($sourceNames as $name) {
                    $names[] = [
                        'address_area_id' => $area->getKey(),
                        'name' => $name['name'],
                        'source' => $providerKey,
                        'name_type' => $name['name_type'] ?? 'alternative',
                        'is_preferred' => $name['is_preferred'] ?? false,
                    ];
                }
            }
            $this->syncSourceRows(AddressingTableResolver::resolve('area_names'), $providerKey, $names, ['address_area_id', 'name', 'name_type'], ['is_preferred'], (string) $country->getKey(), $providerKey, 'address_area_id');

            $relationshipsBySource = $provider->areaRelationships($country);
            $relationshipsTotal = count($relationshipsBySource);
            $relationshipsDone = 0;

            $relationships = [];
            foreach ($relationshipsBySource as $childSourceId => $sourceRelationships) {
                $relationshipsDone++;

                if ($progress !== null) {
                    $progress('relationships', $relationshipsDone, $relationshipsTotal);
                }

                $child = $areas->get($childSourceId);

                if (! $child instanceof AddressArea) {
                    continue;
                }

                foreach ($sourceRelationships as $relationship) {
                    $parent = $areas->get($relationship['parent_source_id']);

                    if (! $parent instanceof AddressArea) {
                        continue;
                    }

                    $relationships[] = [
                        'parent_address_area_id' => $parent->getKey(),
                        'child_address_area_id' => $child->getKey(),
                        'relationship_type' => $relationship['relationship_type'],
                        'hierarchy_type' => $relationship['hierarchy_type'],
                        'source' => $providerKey,
                    ];
                }
            }
            $this->syncSourceRows(AddressingTableResolver::resolve('area_relationships'), $providerKey, $relationships, ['parent_address_area_id', 'child_address_area_id', 'relationship_type', 'hierarchy_type'], [], (string) $country->getKey(), $providerKey, 'child_address_area_id');
        });
    }

    /**
     * @param  list<array<string, mixed>>  $desired
     * @param  list<string>  $keyFields
     * @param  list<string>  $mutableFields
     */
    private function syncSourceRows(string $table, string $source, array $desired, array $keyFields, array $mutableFields, string $countryId, string $providerKey, string $scopeField): void
    {
        $areaIds = ModelResolver::areaClass()::query()
            ->where('country_id', $countryId)
            ->where('metadata->provider', $providerKey)
            ->select('id');
        $existing = $this->connection()->table($table)
            ->where('source', $source)
            ->whereIn($scopeField, $areaIds)
            ->lockForUpdate()
            ->get();
        $existingByKey = [];

        foreach ($existing as $row) {
            $values = (array) $row;
            $existingByKey[$this->rowKey($values, $keyFields)] = $values;
        }

        $wantedByKey = [];
        foreach ($desired as $row) {
            $wantedByKey[$this->rowKey($row, $keyFields)] = $row;
        }

        $now = CarbonImmutable::now()->toDateTimeString();
        $deletes = [];
        $upserts = [];

        foreach ($existingByKey as $key => $row) {
            if (! isset($wantedByKey[$key])) {
                $deletes[] = $row['id'];

                continue;
            }

            $wanted = $wantedByKey[$key];
            unset($wantedByKey[$key]);
            $changed = false;
            foreach ($mutableFields as $field) {
                $same = in_array($field, ['is_primary', 'is_preferred'], true)
                    ? (bool) ($row[$field] ?? false) === (bool) ($wanted[$field] ?? false)
                    : ($row[$field] ?? null) === ($wanted[$field] ?? null);
                if (! $same) {
                    $changed = true;
                }
            }

            if ($changed) {
                $upserts[] = [...$wanted, 'id' => $row['id'], 'created_at' => $row['created_at'], 'updated_at' => $now];
            }
        }

        foreach (array_chunk($deletes, 500) as $chunk) {
            $this->connection()->table($table)->whereIn('id', $chunk)->delete();
        }

        foreach ($wantedByKey as $row) {
            $upserts[] = [...$row, 'id' => (string) Str::uuid7(), 'created_at' => $now, 'updated_at' => $now];
        }

        $updateFields = [...$mutableFields, 'updated_at'];
        foreach (array_chunk($upserts, 500) as $chunk) {
            $this->connection()->table($table)->upsert($chunk, [...$keyFields, 'source'], $updateFields);
        }
    }

    /** @param array<string, mixed> $row
     * @param  list<string>  $fields
     */
    private function rowKey(array $row, array $fields): string
    {
        return implode("\0", array_map(static fn (string $field): string => (string) ($row[$field] ?? ''), $fields));
    }

    /**
     * @param  array<string, array{area_code: string, source: string, area_level: int, hierarchy_types?: list<string>}>  $mappings
     */
    private function linkStateAreas(AddressCountry $country, array $mappings, string $providerKey, ?callable $progress = null): void
    {
        $stateClass = ModelResolver::stateClass();

        $this->connection()->transaction(function () use ($country, $mappings, $providerKey, $stateClass, $progress): void {
            $mappingsTotal = count($mappings);
            $mappingsDone = 0;
            $desired = [];

            foreach ($mappings as $stateCode => $mapping) {
                $mappingsDone++;

                if ($progress !== null) {
                    $progress('states', $mappingsDone, $mappingsTotal);
                }

                $state = $stateClass::query()
                    ->where('country_id', $country->getKey())
                    ->where('code', (string) $stateCode)
                    ->firstOrFail();

                $area = ModelResolver::areaClass()::query()
                    ->where('country_id', $country->getKey())
                    ->where('source', $mapping['source'])
                    ->where('level', $mapping['area_level'])
                    ->where('code', $mapping['area_code'])
                    ->where('is_active', true)
                    ->firstOrFail();

                $hierarchyTypes = $mapping['hierarchy_types'] ?? [''];

                foreach ($hierarchyTypes as $hierarchyType) {
                    $desired[] = [
                        'address_area_id' => $area->getKey(),
                        'state_id' => $state->getKey(),
                        'hierarchy_type' => $hierarchyType,
                        'metadata' => json_encode(['provider' => $providerKey], JSON_THROW_ON_ERROR),
                    ];
                }
            }

            $table = AddressingTableResolver::resolve('area_state_links');
            $existing = $this->connection()->table($table)
                ->whereIn('address_area_id', ModelResolver::areaClass()::query()->where('country_id', $country->getKey())->select('id'))
                ->where('metadata->provider', $providerKey)
                ->lockForUpdate()
                ->get();
            $wanted = [];
            foreach ($desired as $row) {
                $key = $row['address_area_id'] . '|' . $row['state_id'] . '|' . $row['hierarchy_type'];
                $wanted[$key] = $row;
            }

            $deletes = [];
            $upserts = [];
            $now = CarbonImmutable::now()->toDateTimeString();
            foreach ($existing as $row) {
                $key = $row->address_area_id . '|' . $row->state_id . '|' . $row->hierarchy_type;
                if (! isset($wanted[$key])) {
                    $deletes[] = $row->id;

                    continue;
                }

                unset($wanted[$key]);
            }

            foreach (array_chunk($deletes, 500) as $chunk) {
                $this->connection()->table($table)->whereIn('id', $chunk)->delete();
            }

            foreach ($wanted as $row) {
                $upserts[] = [
                    ...$row,
                    'id' => (string) Str::uuid7(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($upserts, 500) as $chunk) {
                $this->connection()->table($table)->insert($chunk);
            }
        });
    }

    private function connection(): ConnectionInterface
    {
        return ModelResolver::countryClass()::query()->getConnection();
    }
}
