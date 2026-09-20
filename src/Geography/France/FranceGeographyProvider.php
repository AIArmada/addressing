<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\France;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class FranceGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_france_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.france';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'FR';
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
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'FR', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/france-address-areas.csv',
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
            '20R' => '20R',
            '971' => '971',
            '972' => '972',
            '973' => '973',
            '974' => '974',
            '976' => '976',
            'ARA' => 'ARA',
            'BFC' => 'BFC',
            'BRE' => 'BRE',
            'CVL' => 'CVL',
            'GES' => 'GES',
            'HDF' => 'HDF',
            'IDF' => 'IDF',
            'NAQ' => 'NAQ',
            'NOR' => 'NOR',
            'OCC' => 'OCC',
            'PAC' => 'PAC',
            'PDL' => 'PDL',
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
            ['name' => 'Corse', 'code' => '20R'],
            ['name' => 'Guadeloupe', 'code' => '971'],
            ['name' => 'Martinique', 'code' => '972'],
            ['name' => 'French Guiana', 'code' => '973'],
            ['name' => 'La Réunion', 'code' => '974'],
            ['name' => 'Mayotte', 'code' => '976'],
            ['name' => 'Auvergne-Rhône-Alpes', 'code' => 'ARA'],
            ['name' => 'Bourgogne-Franche-Comté', 'code' => 'BFC'],
            ['name' => 'Bretagne', 'code' => 'BRE'],
            ['name' => 'Centre-Val de Loire', 'code' => 'CVL'],
            ['name' => 'Grand-Est', 'code' => 'GES'],
            ['name' => 'Hauts-de-France', 'code' => 'HDF'],
            ['name' => 'Île-de-France', 'code' => 'IDF'],
            ['name' => 'Nouvelle-Aquitaine', 'code' => 'NAQ'],
            ['name' => 'Normandie', 'code' => 'NOR'],
            ['name' => 'Occitanie', 'code' => 'OCC'],
            ['name' => 'Provence-Alpes-Côte-d’Azur', 'code' => 'PAC'],
            ['name' => 'Pays-de-la-Loire', 'code' => 'PDL'],
        ];
    }
}
