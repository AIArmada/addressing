<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Australia;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class AustraliaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_australia_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.australia';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'AU';
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
                        key: 'state',
                        label: 'State / Territory',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['state', 'territory'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'lga',
                        label: 'Local Government Area',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['city', 'shire', 'town', 'region', 'borough', 'municipality', 'rural_city', 'council'],
                        areaLevels: [2],
                        parentKey: 'state',
                        assignmentRole: 'lga',
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
                'state' => ['state'],
                'territory' => ['state'],
                'city', 'shire', 'town', 'region', 'borough', 'municipality', 'rural_city', 'council' => ['lga'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'AU', 'is_primary' => true],
                $areaRoles,
            );
        }

        return $roles;
    }

    /** @return array<string, list<array{name: string, name_type?: string, is_preferred?: bool}>> */
    public function areaNames(AddressCountry $country): array
    {
        // Australia Post abbreviations, mirroring the formatter map.
        return [
            'au:state:new-south-wales' => [
                ['name' => 'NSW', 'name_type' => 'abbreviation'],
            ],
            'au:state:queensland' => [
                ['name' => 'QLD', 'name_type' => 'abbreviation'],
            ],
            'au:state:south-australia' => [
                ['name' => 'SA', 'name_type' => 'abbreviation'],
            ],
            'au:state:tasmania' => [
                ['name' => 'TAS', 'name_type' => 'abbreviation'],
            ],
            'au:state:victoria' => [
                ['name' => 'VIC', 'name_type' => 'abbreviation'],
            ],
            'au:state:western-australia' => [
                ['name' => 'WA', 'name_type' => 'abbreviation'],
            ],
            'au:territory:australian-capital-territory' => [
                ['name' => 'ACT', 'name_type' => 'abbreviation'],
            ],
            'au:territory:northern-territory' => [
                ['name' => 'NT', 'name_type' => 'abbreviation'],
            ],
        ];
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
            __DIR__ . '/../../../resources/geography/australia-address-areas.csv',
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
            'ACT' => 'ACT',
            'NSW' => 'NSW',
            'NT' => 'NT',
            'QLD' => 'QLD',
            'SA' => 'SA',
            'TAS' => 'TAS',
            'VIC' => 'VIC',
            'WA' => 'WA',
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
            ['name' => 'Australian Capital Territory', 'code' => 'ACT'],
            ['name' => 'New South Wales', 'code' => 'NSW'],
            ['name' => 'Northern Territory', 'code' => 'NT'],
            ['name' => 'Queensland', 'code' => 'QLD'],
            ['name' => 'South Australia', 'code' => 'SA'],
            ['name' => 'Tasmania', 'code' => 'TAS'],
            ['name' => 'Victoria', 'code' => 'VIC'],
            ['name' => 'Western Australia', 'code' => 'WA'],
        ];
    }
}
