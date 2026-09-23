<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Guatemala;

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

class GuatemalaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_guatemala_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.guatemala';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'GT';
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
                    new AddressLevelDefinition(
                        key: 'municipality',
                        label: 'Municipality',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['municipality'],
                        areaLevels: [2],
                        parentKey: 'department',
                        assignmentRole: 'municipality',
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
            'department' => 'Departamento',
            'municipality' => 'Municipio',
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
                'department' => ['department'],
                'municipality' => ['municipality'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'GT', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/guatemala-address-areas.csv',
            self::AREA_SOURCE,
        );
    }

    /**
     * @return array<int|string, array{area_code: string, source: string, area_level: int, hierarchy_types?: list<string>}>
     */
    public function stateAreaMappings(): array
    {
        /** @var array<int|string, string> */
        $areaCodes = [
            '16' => '16',
            '15' => '15',
            '04' => '04',
            '20' => '20',
            '02' => '02',
            '05' => '05',
            '01' => '01',
            '13' => '13',
            '18' => '18',
            '21' => '21',
            '22' => '22',
            '17' => '17',
            '09' => '09',
            '14' => '14',
            '11' => '11',
            '03' => '03',
            '12' => '12',
            '06' => '06',
            '07' => '07',
            '10' => '10',
            '08' => '08',
            '19' => '19',
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
            ['name' => 'Alta Verapaz', 'code' => '16'],
            ['name' => 'Baja Verapaz', 'code' => '15'],
            ['name' => 'Chimaltenango', 'code' => '04'],
            ['name' => 'Chiquimula', 'code' => '20'],
            ['name' => 'El Progreso', 'code' => '02'],
            ['name' => 'Escuintla', 'code' => '05'],
            ['name' => 'Guatemala', 'code' => '01'],
            ['name' => 'Huehuetenango', 'code' => '13'],
            ['name' => 'Izabal', 'code' => '18'],
            ['name' => 'Jalapa', 'code' => '21'],
            ['name' => 'Jutiapa', 'code' => '22'],
            ['name' => 'Petén', 'code' => '17'],
            ['name' => 'Quetzaltenango', 'code' => '09'],
            ['name' => 'Quiché', 'code' => '14'],
            ['name' => 'Retalhuleu', 'code' => '11'],
            ['name' => 'Sacatepéquez', 'code' => '03'],
            ['name' => 'San Marcos', 'code' => '12'],
            ['name' => 'Santa Rosa', 'code' => '06'],
            ['name' => 'Sololá', 'code' => '07'],
            ['name' => 'Suchitepéquez', 'code' => '10'],
            ['name' => 'Totonicapán', 'code' => '08'],
            ['name' => 'Zacapa', 'code' => '19'],
        ];
    }
}
