<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\NewZealand;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class NewZealandGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_new_zealand_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.new_zealand';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'NZ';
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
                        label: 'Region / Special Island Authority',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['region', 'special_island_authority'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'district',
                        label: 'District / City / Council',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['district', 'city', 'council'],
                        areaLevels: [2],
                        parentKey: 'region',
                        assignmentRole: 'district',
                    ),
                ],
            ),
            new AddressHierarchyDefinition(
                key: 'postal',
                label: 'Postal / Address Geography',
                levels: [
                    new AddressLevelDefinition(
                        key: 'region',
                        label: 'Region / Special Island Authority',
                        kind: 'state',
                        hierarchyType: 'postal',
                        areaTypes: ['region', 'special_island_authority'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'locality',
                        label: 'Locality / Suburb / Town',
                        kind: 'area',
                        hierarchyType: 'postal',
                        areaTypes: ['locality'],
                        areaLevels: [3],
                        parentKey: 'region',
                        assignmentRole: 'postal_locality',
                        refinedBy: 'district',
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
                'special_island_authority' => ['special_island_authority'],
                'district' => ['district'],
                'city' => ['district'],
                'council' => ['district'],
                'locality' => ['postal_locality'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'NZ', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/new-zealand-address-areas.csv',
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
            'AUK' => 'AUK',
            'BOP' => 'BOP',
            'CAN' => 'CAN',
            'CIT' => 'CIT',
            'GIS' => 'GIS',
            'HKB' => 'HKB',
            'MWT' => 'MWT',
            'MBH' => 'MBH',
            'NSN' => 'NSN',
            'NTL' => 'NTL',
            'OTA' => 'OTA',
            'STL' => 'STL',
            'TKI' => 'TKI',
            'TAS' => 'TAS',
            'WKO' => 'WKO',
            'WGN' => 'WGN',
            'WTC' => 'WTC',
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
            ['name' => 'Auckland', 'code' => 'AUK'],
            ['name' => 'Bay of Plenty', 'code' => 'BOP'],
            ['name' => 'Canterbury', 'code' => 'CAN'],
            ['name' => 'Chatham Islands', 'code' => 'CIT'],
            ['name' => 'Gisborne', 'code' => 'GIS'],
            ['name' => 'Hawke\'s Bay', 'code' => 'HKB'],
            ['name' => 'Manawatu-Whanganui', 'code' => 'MWT'],
            ['name' => 'Marlborough', 'code' => 'MBH'],
            ['name' => 'Nelson', 'code' => 'NSN'],
            ['name' => 'Northland', 'code' => 'NTL'],
            ['name' => 'Otago', 'code' => 'OTA'],
            ['name' => 'Southland', 'code' => 'STL'],
            ['name' => 'Taranaki', 'code' => 'TKI'],
            ['name' => 'Tasman', 'code' => 'TAS'],
            ['name' => 'Waikato', 'code' => 'WKO'],
            ['name' => 'Wellington', 'code' => 'WGN'],
            ['name' => 'West Coast', 'code' => 'WTC'],
        ];
    }
}
