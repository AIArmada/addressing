<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\DemocraticRepublicOfCongo;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class DemocraticRepublicOfCongoGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_democratic_republic_of_congo_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.democratic_republic_of_congo';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'CD';
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
                        key: 'province',
                        label: 'Province',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['province'],
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
                'province' => ['province'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'CD', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/democratic-republic-of-congo-address-areas.csv',
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
            'BC' => 'BC',
            'BU' => 'BU',
            'EQ' => 'EQ',
            'HK' => 'HK',
            'HL' => 'HL',
            'HU' => 'HU',
            'IT' => 'IT',
            'KC' => 'KC',
            'KE' => 'KE',
            'KG' => 'KG',
            'KL' => 'KL',
            'KN' => 'KN',
            'KS' => 'KS',
            'LO' => 'LO',
            'LU' => 'LU',
            'MA' => 'MA',
            'MN' => 'MN',
            'MO' => 'MO',
            'NK' => 'NK',
            'NU' => 'NU',
            'SA' => 'SA',
            'SK' => 'SK',
            'SU' => 'SU',
            'TA' => 'TA',
            'TO' => 'TO',
            'TU' => 'TU',
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
            ['name' => 'Kongo Central', 'code' => 'BC'],
            ['name' => 'Bas-Uélé', 'code' => 'BU'],
            ['name' => 'Équateur', 'code' => 'EQ'],
            ['name' => 'Haut-Katanga', 'code' => 'HK'],
            ['name' => 'Haut-Lomami', 'code' => 'HL'],
            ['name' => 'Haut-Uélé', 'code' => 'HU'],
            ['name' => 'Ituri', 'code' => 'IT'],
            ['name' => 'Kasaï Central', 'code' => 'KC'],
            ['name' => 'Kasaï Oriental', 'code' => 'KE'],
            ['name' => 'Kwango', 'code' => 'KG'],
            ['name' => 'Kwilu', 'code' => 'KL'],
            ['name' => 'Kinshasa', 'code' => 'KN'],
            ['name' => 'Kasaï', 'code' => 'KS'],
            ['name' => 'Lomami', 'code' => 'LO'],
            ['name' => 'Lualaba', 'code' => 'LU'],
            ['name' => 'Maniema', 'code' => 'MA'],
            ['name' => 'Mai-Ndombe', 'code' => 'MN'],
            ['name' => 'Mongala', 'code' => 'MO'],
            ['name' => 'Nord-Kivu', 'code' => 'NK'],
            ['name' => 'Nord-Ubangi', 'code' => 'NU'],
            ['name' => 'Sankuru', 'code' => 'SA'],
            ['name' => 'Sud-Kivu', 'code' => 'SK'],
            ['name' => 'Sud-Ubangi', 'code' => 'SU'],
            ['name' => 'Tanganyika', 'code' => 'TA'],
            ['name' => 'Tshopo', 'code' => 'TO'],
            ['name' => 'Tshuapa', 'code' => 'TU'],
        ];
    }
}
