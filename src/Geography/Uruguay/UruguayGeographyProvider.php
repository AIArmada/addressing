<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Uruguay;

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

class UruguayGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_uruguay_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.uruguay';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'UY';
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
                static fn (string $role): array => ['role' => $role, 'country_code' => 'UY', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/uruguay-address-areas.csv',
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
            'AR' => 'AR',
            'CA' => 'CA',
            'CL' => 'CL',
            'CO' => 'CO',
            'DU' => 'DU',
            'FS' => 'FS',
            'FD' => 'FD',
            'LA' => 'LA',
            'MA' => 'MA',
            'MO' => 'MO',
            'PA' => 'PA',
            'RN' => 'RN',
            'RV' => 'RV',
            'RO' => 'RO',
            'SA' => 'SA',
            'SJ' => 'SJ',
            'SO' => 'SO',
            'TA' => 'TA',
            'TT' => 'TT',
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
            ['name' => 'Artigas', 'code' => 'AR'],
            ['name' => 'Canelones', 'code' => 'CA'],
            ['name' => 'Cerro Largo', 'code' => 'CL'],
            ['name' => 'Colonia', 'code' => 'CO'],
            ['name' => 'Durazno', 'code' => 'DU'],
            ['name' => 'Flores', 'code' => 'FS'],
            ['name' => 'Florida', 'code' => 'FD'],
            ['name' => 'Lavalleja', 'code' => 'LA'],
            ['name' => 'Maldonado', 'code' => 'MA'],
            ['name' => 'Montevideo', 'code' => 'MO'],
            ['name' => 'Paysandú', 'code' => 'PA'],
            ['name' => 'Río Negro', 'code' => 'RN'],
            ['name' => 'Rivera', 'code' => 'RV'],
            ['name' => 'Rocha', 'code' => 'RO'],
            ['name' => 'Salto', 'code' => 'SA'],
            ['name' => 'San José', 'code' => 'SJ'],
            ['name' => 'Soriano', 'code' => 'SO'],
            ['name' => 'Tacuarembó', 'code' => 'TA'],
            ['name' => 'Treinta y Tres', 'code' => 'TT'],
        ];
    }
}
