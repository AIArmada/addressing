<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Models\AddressArea;
use AIArmada\Addressing\Models\AddressAreaName;
use AIArmada\Addressing\Models\AddressAreaRelationship;
use AIArmada\Addressing\Models\AddressAreaRole;
use AIArmada\Addressing\Models\AddressAreaStateLink;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\ModelResolver;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\DB;
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

            $country = AddressCountry::query()->where('iso2', $providerCode)->first();

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

            $areaSummary = DB::transaction(function () use ($provider, $country, $providerCode, $report): ?array {
                $provider->seed($country);

                if (! $provider instanceof CountryHierarchyProvider) {
                    return null;
                }

                $providerKey = mb_trim($provider->providerKey());

                if ($providerKey === '') {
                    throw new InvalidArgumentException('Addressing geography provider keys cannot be empty.');
                }

                $areaSource = $provider->addressAreaSource();
                AddressArea::query()
                    ->where('country_id', $country->getKey())
                    ->where('metadata->provider', $providerKey)
                    ->update(['is_active' => false]);
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
        DB::transaction(function () use ($country, $provider, $providerKey, $progress): void {
            $providerAreas = AddressArea::query()
                ->where('country_id', $country->getKey())
                ->where('metadata->provider', $providerKey)
                ->get();
            $areaIds = $providerAreas->modelKeys();

            foreach (array_chunk($areaIds, 500) as $idChunk) {
                AddressAreaRole::query()
                    ->whereIn('address_area_id', $idChunk)
                    ->where('source', $providerKey)
                    ->delete();
            }

            $areas = $providerAreas
                ->where('is_active', true)
                ->keyBy('source_id');
            foreach (array_chunk($areaIds, 500) as $idChunk) {
                AddressAreaName::query()
                    ->whereIn('address_area_id', $idChunk)
                    ->where('source', $providerKey)
                    ->delete();
                AddressAreaRelationship::query()
                    ->whereIn('child_address_area_id', $idChunk)
                    ->where('source', $providerKey)
                    ->delete();
            }

            $rolesBySource = $provider->areaRoles($country);
            $rolesTotal = count($rolesBySource);
            $rolesDone = 0;

            foreach ($rolesBySource as $sourceId => $roles) {
                $rolesDone++;

                if ($progress !== null) {
                    $progress('roles', $rolesDone, $rolesTotal);
                }

                $area = $areas->get($sourceId);

                if (! $area instanceof AddressArea) {
                    continue;
                }

                foreach ($roles as $role) {
                    AddressAreaRole::query()->create([
                        'address_area_id' => $area->getKey(),
                        'role' => $role['role'],
                        'source' => $providerKey,
                        'country_code' => $role['country_code'] ?? $country->iso2,
                        'is_primary' => $role['is_primary'] ?? false,
                    ]);
                }
            }

            $namesBySource = $provider->areaNames($country);
            $namesTotal = count($namesBySource);
            $namesDone = 0;

            foreach ($namesBySource as $sourceId => $names) {
                $namesDone++;

                if ($progress !== null) {
                    $progress('names', $namesDone, $namesTotal);
                }

                $area = $areas->get($sourceId);

                if (! $area instanceof AddressArea) {
                    continue;
                }

                foreach ($names as $name) {
                    AddressAreaName::query()->create([
                        'address_area_id' => $area->getKey(),
                        'name' => $name['name'],
                        'source' => $providerKey,
                        'name_type' => $name['name_type'] ?? 'alternative',
                        'is_preferred' => $name['is_preferred'] ?? false,
                    ]);
                }
            }

            $relationshipsBySource = $provider->areaRelationships($country);
            $relationshipsTotal = count($relationshipsBySource);
            $relationshipsDone = 0;

            foreach ($relationshipsBySource as $childSourceId => $relationships) {
                $relationshipsDone++;

                if ($progress !== null) {
                    $progress('relationships', $relationshipsDone, $relationshipsTotal);
                }

                $child = $areas->get($childSourceId);

                if (! $child instanceof AddressArea) {
                    continue;
                }

                foreach ($relationships as $relationship) {
                    $parent = $areas->get($relationship['parent_source_id']);

                    if (! $parent instanceof AddressArea) {
                        continue;
                    }

                    AddressAreaRelationship::query()->create([
                        'parent_address_area_id' => $parent->getKey(),
                        'child_address_area_id' => $child->getKey(),
                        'relationship_type' => $relationship['relationship_type'],
                        'hierarchy_type' => $relationship['hierarchy_type'],
                        'source' => $providerKey,
                    ]);
                }
            }
        });
    }

    /**
     * @param  array<string, array{area_code: string, source: string, area_level: int, hierarchy_types?: list<string>}>  $mappings
     */
    private function linkStateAreas(AddressCountry $country, array $mappings, string $providerKey, ?callable $progress = null): void
    {
        $stateClass = ModelResolver::stateClass();

        DB::transaction(function () use ($country, $mappings, $providerKey, $stateClass, $progress): void {
            AddressAreaStateLink::query()
                ->where('metadata->provider', $providerKey)
                ->whereHas('addressArea', fn ($query) => $query->where('country_id', $country->getKey()))
                ->delete();

            $mappingsTotal = count($mappings);
            $mappingsDone = 0;

            foreach ($mappings as $stateCode => $mapping) {
                $mappingsDone++;

                if ($progress !== null) {
                    $progress('states', $mappingsDone, $mappingsTotal);
                }

                $state = $stateClass::query()
                    ->where('country_id', $country->getKey())
                    ->where('code', (string) $stateCode)
                    ->firstOrFail();

                $area = AddressArea::query()
                    ->where('country_id', $country->getKey())
                    ->where('source', $mapping['source'])
                    ->where('level', $mapping['area_level'])
                    ->where('code', $mapping['area_code'])
                    ->where('is_active', true)
                    ->firstOrFail();

                $hierarchyTypes = $mapping['hierarchy_types'] ?? [null];

                foreach ($hierarchyTypes as $hierarchyType) {
                    AddressAreaStateLink::query()->updateOrCreate(
                        [
                            'address_area_id' => $area->getKey(),
                            'state_id' => $state->getKey(),
                            'hierarchy_type' => $hierarchyType,
                        ],
                        ['metadata' => ['provider' => $providerKey]],
                    );
                }
            }
        });
    }
}
