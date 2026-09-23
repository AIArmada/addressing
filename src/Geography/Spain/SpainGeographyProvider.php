<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Spain;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryAreaTypeLabelProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class SpainGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_spain_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.spain';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'ES';
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
                        key: 'community',
                        label: 'Autonomous Community / City',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['autonomous_community', 'autonomous_city'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'province',
                        label: 'Province',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['province'],
                        areaLevels: [2],
                        parentKey: 'community',
                        assignmentRole: 'province',
                    ),
                ],
            ),
        ];
    }

    /** @return array<string, string> */
    public function areaTypeLabels(): array
    {
        // Spanish administrative terms.
        return [
            'autonomous_community' => 'Comunidad Autónoma',
            'autonomous_city' => 'Ciudad Autónoma',
            'province' => 'Provincia',
        ];
    }

    /** @return list<array{state_code: string, type_labels: array<string, string>}> */
    public function stateAreaTypeLabels(): array
    {
        return [];
    }

    /** @return array<string, list<array{role: string, country_code?: string, is_primary?: bool}>> */
    public function areaRoles(AddressCountry $country): array
    {
        $roles = [];

        foreach ($this->addressAreaSource()->areas() as $area) {
            $areaRoles = match ($area->type) {
                'autonomous_community' => ['community'],
                'autonomous_city' => ['community'],
                'province' => ['province'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'ES', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/spain-address-areas.csv',
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
            'AN' => 'AN',
            'AR' => 'AR',
            'AS' => 'AS',
            'CB' => 'CB',
            'CE' => 'CE',
            'CL' => 'CL',
            'CM' => 'CM',
            'CN' => 'CN',
            'CT' => 'CT',
            'EX' => 'EX',
            'GA' => 'GA',
            'IB' => 'IB',
            'MC' => 'MC',
            'MD' => 'MD',
            'ML' => 'ML',
            'NC' => 'NC',
            'PV' => 'PV',
            'RI' => 'RI',
            'VC' => 'VC',
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
            ['name' => 'Andalusia', 'code' => 'AN'],
            ['name' => 'Aragon', 'code' => 'AR'],
            ['name' => 'Asturias', 'code' => 'AS'],
            ['name' => 'Cantabria', 'code' => 'CB'],
            ['name' => 'Ceuta', 'code' => 'CE'],
            ['name' => 'Castile and León', 'code' => 'CL'],
            ['name' => 'Castilla-La Mancha', 'code' => 'CM'],
            ['name' => 'Canary Islands', 'code' => 'CN'],
            ['name' => 'Catalonia', 'code' => 'CT'],
            ['name' => 'Extremadura', 'code' => 'EX'],
            ['name' => 'Galicia', 'code' => 'GA'],
            ['name' => 'Balearic Islands', 'code' => 'IB'],
            ['name' => 'Murcia', 'code' => 'MC'],
            ['name' => 'Madrid', 'code' => 'MD'],
            ['name' => 'Melilla', 'code' => 'ML'],
            ['name' => 'Navarre', 'code' => 'NC'],
            ['name' => 'Basque Country', 'code' => 'PV'],
            ['name' => 'La Rioja', 'code' => 'RI'],
            ['name' => 'Valencian Community', 'code' => 'VC'],
            ['name' => 'Almería', 'code' => 'AL'],
            ['name' => 'Cádiz', 'code' => 'CA'],
            ['name' => 'Córdoba', 'code' => 'CO'],
            ['name' => 'Granada', 'code' => 'GR'],
            ['name' => 'Huelva', 'code' => 'H'],
            ['name' => 'Jaén', 'code' => 'J'],
            ['name' => 'Málaga', 'code' => 'MA'],
            ['name' => 'Sevilla', 'code' => 'SE'],
            ['name' => 'Huesca', 'code' => 'HU'],
            ['name' => 'Teruel', 'code' => 'TE'],
            ['name' => 'Zaragoza', 'code' => 'Z'],
            ['name' => 'Asturias', 'code' => 'O'],
            ['name' => 'Las Palmas', 'code' => 'GC'],
            ['name' => 'Santa Cruz de Tenerife', 'code' => 'TF'],
            ['name' => 'Cantabria', 'code' => 'S'],
            ['name' => 'Ávila', 'code' => 'AV'],
            ['name' => 'Burgos', 'code' => 'BU'],
            ['name' => 'León', 'code' => 'LE'],
            ['name' => 'Palencia', 'code' => 'P'],
            ['name' => 'Salamanca', 'code' => 'SA'],
            ['name' => 'Segovia', 'code' => 'SG'],
            ['name' => 'Soria', 'code' => 'SO'],
            ['name' => 'Valladolid', 'code' => 'VA'],
            ['name' => 'Zamora', 'code' => 'ZA'],
            ['name' => 'Albacete', 'code' => 'AB'],
            ['name' => 'Ciudad Real', 'code' => 'CR'],
            ['name' => 'Cuenca', 'code' => 'CU'],
            ['name' => 'Guadalajara', 'code' => 'GU'],
            ['name' => 'Toledo', 'code' => 'TO'],
            ['name' => 'Barcelona', 'code' => 'B'],
            ['name' => 'Girona', 'code' => 'GI'],
            ['name' => 'Lleida', 'code' => 'L'],
            ['name' => 'Tarragona', 'code' => 'T'],
            ['name' => 'Badajoz', 'code' => 'BA'],
            ['name' => 'Cáceres', 'code' => 'CC'],
            ['name' => 'A Coruña', 'code' => 'C'],
            ['name' => 'Lugo', 'code' => 'LU'],
            ['name' => 'Ourense', 'code' => 'OR'],
            ['name' => 'Pontevedra', 'code' => 'PO'],
            ['name' => 'Illes Balears', 'code' => 'PM'],
            ['name' => 'La Rioja', 'code' => 'LO'],
            ['name' => 'Madrid', 'code' => 'M'],
            ['name' => 'Murcia', 'code' => 'MU'],
            ['name' => 'Navarra', 'code' => 'NA'],
            ['name' => 'Bizkaia', 'code' => 'BI'],
            ['name' => 'Gipuzkoa', 'code' => 'SS'],
            ['name' => 'Araba', 'code' => 'VI'],
            ['name' => 'Alicante', 'code' => 'A'],
            ['name' => 'Castellón', 'code' => 'CS'],
            ['name' => 'Valencia', 'code' => 'V'],
        ];
    }
}
