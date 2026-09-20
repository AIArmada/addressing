<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Mexico;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class MexicoGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_mexico_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.mexico';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'MX';
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
                        label: 'State',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['state'],
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
                'state' => ['state'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'MX', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/mexico-address-areas.csv',
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
            'AGU' => 'AGU',
            'BCN' => 'BCN',
            'BCS' => 'BCS',
            'CAM' => 'CAM',
            'CHH' => 'CHH',
            'CHP' => 'CHP',
            'CMX' => 'CMX',
            'COA' => 'COA',
            'COL' => 'COL',
            'DUR' => 'DUR',
            'GRO' => 'GRO',
            'GUA' => 'GUA',
            'HID' => 'HID',
            'JAL' => 'JAL',
            'MEX' => 'MEX',
            'MIC' => 'MIC',
            'MOR' => 'MOR',
            'NAY' => 'NAY',
            'NLE' => 'NLE',
            'OAX' => 'OAX',
            'PUE' => 'PUE',
            'QUE' => 'QUE',
            'ROO' => 'ROO',
            'SIN' => 'SIN',
            'SLP' => 'SLP',
            'SON' => 'SON',
            'TAB' => 'TAB',
            'TAM' => 'TAM',
            'TLA' => 'TLA',
            'VER' => 'VER',
            'YUC' => 'YUC',
            'ZAC' => 'ZAC',
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
            ['name' => 'Aguascalientes', 'code' => 'AGU'],
            ['name' => 'Baja California', 'code' => 'BCN'],
            ['name' => 'Baja California Sur', 'code' => 'BCS'],
            ['name' => 'Campeche', 'code' => 'CAM'],
            ['name' => 'Chihuahua', 'code' => 'CHH'],
            ['name' => 'Chiapas', 'code' => 'CHP'],
            ['name' => 'Ciudad de México', 'code' => 'CMX'],
            ['name' => 'Coahuila de Zaragoza', 'code' => 'COA'],
            ['name' => 'Colima', 'code' => 'COL'],
            ['name' => 'Durango', 'code' => 'DUR'],
            ['name' => 'Guerrero', 'code' => 'GRO'],
            ['name' => 'Guanajuato', 'code' => 'GUA'],
            ['name' => 'Hidalgo', 'code' => 'HID'],
            ['name' => 'Jalisco', 'code' => 'JAL'],
            ['name' => 'Estado de México', 'code' => 'MEX'],
            ['name' => 'Michoacán de Ocampo', 'code' => 'MIC'],
            ['name' => 'Morelos', 'code' => 'MOR'],
            ['name' => 'Nayarit', 'code' => 'NAY'],
            ['name' => 'Nuevo León', 'code' => 'NLE'],
            ['name' => 'Oaxaca', 'code' => 'OAX'],
            ['name' => 'Puebla', 'code' => 'PUE'],
            ['name' => 'Querétaro', 'code' => 'QUE'],
            ['name' => 'Quintana Roo', 'code' => 'ROO'],
            ['name' => 'Sinaloa', 'code' => 'SIN'],
            ['name' => 'San Luis Potosí', 'code' => 'SLP'],
            ['name' => 'Sonora', 'code' => 'SON'],
            ['name' => 'Tabasco', 'code' => 'TAB'],
            ['name' => 'Tamaulipas', 'code' => 'TAM'],
            ['name' => 'Tlaxcala', 'code' => 'TLA'],
            ['name' => 'Veracruz de Ignacio de la Llave', 'code' => 'VER'],
            ['name' => 'Yucatán', 'code' => 'YUC'],
            ['name' => 'Zacatecas', 'code' => 'ZAC'],
        ];
    }
}
