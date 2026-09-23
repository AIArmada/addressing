<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Ghana;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class GhanaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_ghana_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.ghana';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'GH';
    }

    public function seed(AddressCountry $country): void
    {
        $stateClass = ModelResolver::stateClass();
        $statesData = $this->stateDefinitions();

        foreach ($statesData as $s) {
            $stateClass::updateOrCreate(
                ['country_id' => $country->id, 'code' => $s['code']],
                [
                    'name' => $s['name'],
                    'country_code' => $this->countryCode(),
                ],
            );
        }
    }

    /** @return list<AddressHierarchyDefinition> */
    public function addressHierarchies(): array
    {
        return [
            new AddressHierarchyDefinition(
                key: 'administrative',
                label: 'Administrative / Territorial Geography',
                levels: [
                    new AddressLevelDefinition(
                        key: 'region',
                        label: 'Region',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['region'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'district',
                        label: 'Metropolitan / Municipal / District',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['metropolitan_city', 'municipality', 'district'],
                        areaLevels: [2],
                        parentKey: 'region',
                        assignmentRole: 'district',
                    ),
                ],
            ),
        ];
    }

    /** @return array<string, list<array{role: string, country_code?: string, is_primary?: bool}>> */
    public function areaRoles(AddressCountry $country): array
    {
        $roles = [];

        foreach ($this->addressAreaSource()->areas() as $area) {
            $areaRoles = match ($area->type) {
                'region' => ['region'],
                'metropolitan_city' => ['district'],
                'municipality' => ['district'],
                'district' => ['district'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'GH', 'is_primary' => true],
                $areaRoles,
            );
        }

        return $roles;
    }

    /** @return array<string, list<array{name: string, name_type?: string, is_preferred?: bool}>> */
    public function areaNames(AddressCountry $country): array
    {
        return [];
    }

    /** @return array<string, list<array{parent_source_id: string, relationship_type: string, hierarchy_type: string}>> */
    public function areaRelationships(AddressCountry $country): array
    {
        $relationships = [];

        foreach ($this->addressAreaSource()->areas() as $area) {
            if ($area->parentSourceId === null) {
                continue;
            }

            $relationships[$area->sourceId][] = [
                'parent_source_id' => $area->parentSourceId,
                'relationship_type' => 'contains',
                'hierarchy_type' => 'administrative',
            ];
        }

        return $relationships;
    }

    public function addressAreaSource(): AddressAreaSource
    {
        return new CsvAddressAreaSource(
            __DIR__ . '/../../../resources/geography/ghana-address-areas.csv',
            self::AREA_SOURCE,
        );
    }

    /**
     * @return array<string, array{area_code: string, source: string, area_level: int, hierarchy_types?: list<string>}>
     */
    public function stateAreaMappings(): array
    {
        /** @var array<string, string> */
        $areaCodes = [
            'AA' => 'AA',
            'AF' => 'AF',
            'AH' => 'AH',
            'BE' => 'BE',
            'BO' => 'BO',
            'CP' => 'CP',
            'EP' => 'EP',
            'NE' => 'NE',
            'NP' => 'NP',
            'OT' => 'OT',
            'SV' => 'SV',
            'TV' => 'TV',
            'UE' => 'UE',
            'UW' => 'UW',
            'WN' => 'WN',
            'WP' => 'WP',
        ];

        return array_map(
            static fn (string $areaCode): array => [
                'area_code' => $areaCode,
                'source' => self::AREA_SOURCE,
                'area_level' => 1,
                'hierarchy_types' => ['administrative'],
            ],
            $areaCodes,
        );
    }

    /**
     * @return list<array{name: string, code: string}>
     */
    private function stateDefinitions(): array
    {
        return [
            ['name' => 'Greater Accra', 'code' => 'AA'],
            ['name' => 'Ahafo', 'code' => 'AF'],
            ['name' => 'Ashanti', 'code' => 'AH'],
            ['name' => 'Bono East', 'code' => 'BE'],
            ['name' => 'Bono', 'code' => 'BO'],
            ['name' => 'Central', 'code' => 'CP'],
            ['name' => 'Eastern', 'code' => 'EP'],
            ['name' => 'North East', 'code' => 'NE'],
            ['name' => 'Northern', 'code' => 'NP'],
            ['name' => 'Oti', 'code' => 'OT'],
            ['name' => 'Savannah', 'code' => 'SV'],
            ['name' => 'Volta', 'code' => 'TV'],
            ['name' => 'Upper East', 'code' => 'UE'],
            ['name' => 'Upper West', 'code' => 'UW'],
            ['name' => 'Western North', 'code' => 'WN'],
            ['name' => 'Western', 'code' => 'WP'],
        ];
    }
}
