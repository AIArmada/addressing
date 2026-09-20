<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\ElSalvador;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class ElSalvadorGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_el_salvador_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.el_salvador';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'SV';
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
                        key: 'department',
                        label: 'Department',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['department'],
                        areaLevel: 1,
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
                'department' => ['department'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'SV', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/el-salvador-address-areas.csv',
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
            'AH' => 'AH',
            'CA' => 'CA',
            'CH' => 'CH',
            'CU' => 'CU',
            'LI' => 'LI',
            'PA' => 'PA',
            'UN' => 'UN',
            'MO' => 'MO',
            'SM' => 'SM',
            'SS' => 'SS',
            'SV' => 'SV',
            'SA' => 'SA',
            'SO' => 'SO',
            'US' => 'US',
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
            ['name' => 'Ahuachapán', 'code' => 'AH'],
            ['name' => 'Cabañas', 'code' => 'CA'],
            ['name' => 'Chalatenango', 'code' => 'CH'],
            ['name' => 'Cuscatlán', 'code' => 'CU'],
            ['name' => 'La Libertad', 'code' => 'LI'],
            ['name' => 'La Paz', 'code' => 'PA'],
            ['name' => 'La Unión', 'code' => 'UN'],
            ['name' => 'Morazán', 'code' => 'MO'],
            ['name' => 'San Miguel', 'code' => 'SM'],
            ['name' => 'San Salvador', 'code' => 'SS'],
            ['name' => 'San Vicente', 'code' => 'SV'],
            ['name' => 'Santa Ana', 'code' => 'SA'],
            ['name' => 'Sonsonate', 'code' => 'SO'],
            ['name' => 'Usulután', 'code' => 'US'],
        ];
    }
}
