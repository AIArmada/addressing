<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Colombia;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class ColombiaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_colombia_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.colombia';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'CO';
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
                        label: 'Department / Capital District',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['department', 'capital_district'],
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
                'capital_district' => ['department'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'CO', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/colombia-address-areas.csv',
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
            'AMA' => 'AMA',
            'ANT' => 'ANT',
            'ARA' => 'ARA',
            'ATL' => 'ATL',
            'BOL' => 'BOL',
            'BOY' => 'BOY',
            'CAL' => 'CAL',
            'CAQ' => 'CAQ',
            'CAS' => 'CAS',
            'CAU' => 'CAU',
            'CES' => 'CES',
            'CHO' => 'CHO',
            'COR' => 'COR',
            'CUN' => 'CUN',
            'DC' => 'DC',
            'GUA' => 'GUA',
            'GUV' => 'GUV',
            'HUI' => 'HUI',
            'LAG' => 'LAG',
            'MAG' => 'MAG',
            'MET' => 'MET',
            'NAR' => 'NAR',
            'NSA' => 'NSA',
            'PUT' => 'PUT',
            'QUI' => 'QUI',
            'RIS' => 'RIS',
            'SAN' => 'SAN',
            'SAP' => 'SAP',
            'SUC' => 'SUC',
            'TOL' => 'TOL',
            'VAC' => 'VAC',
            'VAU' => 'VAU',
            'VID' => 'VID',
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
            ['name' => 'Amazonas', 'code' => 'AMA'],
            ['name' => 'Antioquia', 'code' => 'ANT'],
            ['name' => 'Arauca', 'code' => 'ARA'],
            ['name' => 'Atlántico', 'code' => 'ATL'],
            ['name' => 'Bolívar', 'code' => 'BOL'],
            ['name' => 'Boyacá', 'code' => 'BOY'],
            ['name' => 'Caldas', 'code' => 'CAL'],
            ['name' => 'Caquetá', 'code' => 'CAQ'],
            ['name' => 'Casanare', 'code' => 'CAS'],
            ['name' => 'Cauca', 'code' => 'CAU'],
            ['name' => 'Cesar', 'code' => 'CES'],
            ['name' => 'Chocó', 'code' => 'CHO'],
            ['name' => 'Córdoba', 'code' => 'COR'],
            ['name' => 'Cundinamarca', 'code' => 'CUN'],
            ['name' => 'Bogotá D.C.', 'code' => 'DC'],
            ['name' => 'Guainía', 'code' => 'GUA'],
            ['name' => 'Guaviare', 'code' => 'GUV'],
            ['name' => 'Huila', 'code' => 'HUI'],
            ['name' => 'La Guajira', 'code' => 'LAG'],
            ['name' => 'Magdalena', 'code' => 'MAG'],
            ['name' => 'Meta', 'code' => 'MET'],
            ['name' => 'Nariño', 'code' => 'NAR'],
            ['name' => 'Norte de Santander', 'code' => 'NSA'],
            ['name' => 'Putumayo', 'code' => 'PUT'],
            ['name' => 'Quindío', 'code' => 'QUI'],
            ['name' => 'Risaralda', 'code' => 'RIS'],
            ['name' => 'Santander', 'code' => 'SAN'],
            ['name' => 'San Andrés, Providencia y Santa Catalina', 'code' => 'SAP'],
            ['name' => 'Sucre', 'code' => 'SUC'],
            ['name' => 'Tolima', 'code' => 'TOL'],
            ['name' => 'Valle del Cauca', 'code' => 'VAC'],
            ['name' => 'Vaupés', 'code' => 'VAU'],
            ['name' => 'Vichada', 'code' => 'VID'],
        ];
    }
}
