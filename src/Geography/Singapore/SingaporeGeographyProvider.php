<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Singapore;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class SingaporeGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_singapore_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.singapore';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'SG';
    }

    public function seed(AddressCountry $singapore): void
    {
        $stateClass = ModelResolver::stateClass();
        $statesData = $this->stateDefinitions();

        foreach ($statesData as $s) {
            $stateClass::updateOrCreate(
                ['country_id' => $singapore->id, 'code' => $s['code']],
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
                key: 'postal',
                label: 'Postal / Delivery Geography',
                levels: [
                    new AddressLevelDefinition(
                        key: 'postal_district',
                        label: 'Postal District',
                        kind: 'area',
                        hierarchyType: 'postal',
                        areaTypes: ['postal_district'],
                        areaLevel: 1,
                        assignmentRole: 'postal_district',
                    ),
                    new AddressLevelDefinition(
                        key: 'postal_sector',
                        label: 'Postal Sector',
                        kind: 'area',
                        hierarchyType: 'postal',
                        areaTypes: ['postal_sector'],
                        areaLevels: [2],
                        parentKey: 'postal_district',
                        assignmentRole: 'postal_sector',
                    ),
                ],
            ),
            new AddressHierarchyDefinition(
                key: 'administrative',
                label: 'Administrative / Planning Geography',
                levels: [
                    new AddressLevelDefinition(
                        key: 'region',
                        label: 'Planning Region',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['region'],
                        areaLevel: 1,
                        assignmentRole: 'region',
                    ),
                    new AddressLevelDefinition(
                        key: 'planning_area',
                        label: 'Planning Area',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['planning_area'],
                        areaLevels: [2],
                        parentKey: 'region',
                        assignmentRole: 'planning_area',
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
                'planning_area' => ['planning_area'],
                'postal_district' => ['postal_district'],
                'postal_sector' => ['postal_sector'],
                'district' => ['district'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'SG', 'is_primary' => true],
                $areaRoles,
            );
        }

        return $roles;
    }

    /** @return array<string, list<array{name: string, name_type?: string, is_preferred?: bool}>> */
    public function areaNames(AddressCountry $country): array
    {
        return [
            'sg:planning-area:yishun' => [
                ['name' => 'Nee Soon', 'name_type' => 'historic'],
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
                'hierarchy_type' => $area->type === 'postal_sector' ? 'postal' : 'administrative',
            ];
        }

        return $relationships;
    }

    public function addressAreaSource(): AddressAreaSource
    {
        return new CsvAddressAreaSource(
            __DIR__ . '/../../../resources/geography/singapore-address-areas.csv',
            self::AREA_SOURCE,
        );
    }

    /**
     * CDC districts do not nest inside the URA planning tree or the postal
     * tree, so the links stay hierarchy-agnostic instead of claiming a root.
     *
     * @return array<string, array{area_code: string, source: string, area_level: int, hierarchy_types?: list<string>}>
     */
    public function stateAreaMappings(): array
    {
        /** @var array<string, string> */
        $areaCodes = [
            '01' => 'cdc-01',
            '02' => 'cdc-02',
            '03' => 'cdc-03',
            '04' => 'cdc-04',
            '05' => 'cdc-05',
        ];

        return array_map(
            static fn (string $areaCode): array => [
                'area_code' => $areaCode,
                'source' => self::AREA_SOURCE,
                'area_level' => 1,
            ],
            $areaCodes,
        );
    }

    /**
     * ISO 3166-2:SG community development council districts.
     *
     * @return list<array{name: string, code: string}>
     */
    private function stateDefinitions(): array
    {
        return [
            ['name' => 'Central Singapore', 'code' => '01'],
            ['name' => 'North East', 'code' => '02'],
            ['name' => 'North West', 'code' => '03'],
            ['name' => 'South East', 'code' => '04'],
            ['name' => 'South West', 'code' => '05'],
        ];
    }
}
