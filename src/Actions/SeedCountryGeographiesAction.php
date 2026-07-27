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
     * @return array{seeded: list<string>, skipped: list<string>, areas: array}
     */
    public function execute(?string $countryCode = null): array
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

            $provider->seed($country);

            if ($provider instanceof CountryHierarchyProvider) {
                $areaSource = $provider->addressAreaSource();
                $areaResult = $this->importAddressAreas->execute($areaSource);

                if ($areaResult->hasFailures()) {
                    throw new InvalidArgumentException(sprintf(
                        'Cannot seed %s address hierarchy because %d area rows failed.',
                        $providerCode,
                        count($areaResult->failures),
                    ));
                }

                $this->linkStateAreas($country, $provider->stateAreaMappings());

                if ($provider instanceof CountryAddressAreaMetadataProvider) {
                    $this->syncAreaMetadata($country, $provider, $areaSource->key());
                }
                $areas[$providerCode] = [
                    'created' => $areaResult->created,
                    'updated' => $areaResult->updated,
                    'skipped' => $areaResult->skipped,
                ];
            }

            $seeded[] = $providerCode;
        }

        return [
            'seeded' => array_values(array_unique($seeded)),
            'skipped' => array_values(array_unique($skipped)),
            'areas' => $areas,
        ];
    }

    private function syncAreaMetadata(AddressCountry $country, CountryAddressAreaMetadataProvider $provider, string $source): void
    {
        DB::transaction(function () use ($country, $provider, $source): void {
            $areas = AddressArea::query()
                ->where('country_id', $country->getKey())
                ->where('source', $source)
                ->get()
                ->keyBy('source_id');
            $areaIds = $areas->modelKeys();

            AddressAreaRole::query()->whereIn('address_area_id', $areaIds)->delete();
            AddressAreaName::query()->whereIn('address_area_id', $areaIds)->delete();
            AddressAreaRelationship::query()
                ->whereIn('child_address_area_id', $areaIds)
                ->where('metadata->source', $source)
                ->delete();

            foreach ($provider->areaRoles($country) as $sourceId => $roles) {
                $area = $areas->get($sourceId);

                if (! $area instanceof AddressArea) {
                    continue;
                }

                foreach ($roles as $role) {
                    AddressAreaRole::query()->create([
                        'address_area_id' => $area->getKey(),
                        'role' => $role['role'],
                        'country_code' => $role['country_code'] ?? $country->iso2,
                        'is_primary' => $role['is_primary'] ?? false,
                    ]);
                }
            }

            foreach ($provider->areaNames($country) as $sourceId => $names) {
                $area = $areas->get($sourceId);

                if (! $area instanceof AddressArea) {
                    continue;
                }

                foreach ($names as $name) {
                    AddressAreaName::query()->create([
                        'address_area_id' => $area->getKey(),
                        'name' => $name['name'],
                        'name_type' => $name['name_type'] ?? 'alternative',
                        'is_preferred' => $name['is_preferred'] ?? false,
                    ]);
                }
            }

            foreach ($provider->areaRelationships($country) as $childSourceId => $relationships) {
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
                        'metadata' => ['source' => $source],
                    ]);
                }
            }
        });
    }

    /**
     * @param  array<string, array{area_code: string, source: string, area_level: int}>  $mappings
     */
    private function linkStateAreas(AddressCountry $country, array $mappings): void
    {
        $stateClass = ModelResolver::stateClass();

        DB::transaction(function () use ($country, $mappings, $stateClass): void {
            foreach ($mappings as $stateCode => $mapping) {
                $state = $stateClass::query()
                    ->where('country_id', $country->getKey())
                    ->where('code', $stateCode)
                    ->firstOrFail();

                $area = AddressArea::query()
                    ->where('country_id', $country->getKey())
                    ->where('source', $mapping['source'])
                    ->where('level', $mapping['area_level'])
                    ->where('code', $mapping['area_code'])
                    ->firstOrFail();

                AddressAreaStateLink::query()->updateOrCreate(
                    [
                        'address_area_id' => $area->getKey(),
                        'state_id' => $state->getKey(),
                    ],
                    ['metadata' => ['provider' => $country->iso2]],
                );
            }
        });
    }
}
