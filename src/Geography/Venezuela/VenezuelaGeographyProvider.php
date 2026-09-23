<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Venezuela;

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

class VenezuelaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_venezuela_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.venezuela';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'VE';
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
                        label: 'State / Capital District / Federal Dependency',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['state', 'capital_district', 'federal_dependency'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'municipality',
                        label: 'Municipality',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['municipality'],
                        areaLevels: [2],
                        parentKey: 'state',
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
            'state' => 'Estado',
            'capital_district' => 'Distrito Capital',
            'federal_dependency' => 'Dependencias Federales',
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
            // The capital district sits in the state tier; the childless federal dependency stays distinct.
            $areaRoles = match ($area->type) {
                'state' => ['state'],
                'capital_district' => ['state'],
                'federal_dependency' => ['federal_dependency'],
                'municipality' => ['municipality'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'VE', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/venezuela-address-areas.csv',
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
            'Z' => 'Z',
            'B' => 'B',
            'C' => 'C',
            'D' => 'D',
            'E' => 'E',
            'F' => 'F',
            'G' => 'G',
            'H' => 'H',
            'Y' => 'Y',
            'A' => 'A',
            'I' => 'I',
            'J' => 'J',
            'X' => 'X',
            'K' => 'K',
            'L' => 'L',
            'M' => 'M',
            'N' => 'N',
            'O' => 'O',
            'P' => 'P',
            'R' => 'R',
            'S' => 'S',
            'T' => 'T',
            'W' => 'W',
            'U' => 'U',
            'V' => 'V',
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
            ['name' => 'Amazonas', 'code' => 'Z'],
            ['name' => 'Anzoátegui', 'code' => 'B'],
            ['name' => 'Apure', 'code' => 'C'],
            ['name' => 'Aragua', 'code' => 'D'],
            ['name' => 'Barinas', 'code' => 'E'],
            ['name' => 'Bolívar', 'code' => 'F'],
            ['name' => 'Carabobo', 'code' => 'G'],
            ['name' => 'Cojedes', 'code' => 'H'],
            ['name' => 'Delta Amacuro', 'code' => 'Y'],
            ['name' => 'Distrito Capital', 'code' => 'A'],
            ['name' => 'Falcón', 'code' => 'I'],
            ['name' => 'Guárico', 'code' => 'J'],
            ['name' => 'La Guaira', 'code' => 'X'],
            ['name' => 'Lara', 'code' => 'K'],
            ['name' => 'Mérida', 'code' => 'L'],
            ['name' => 'Miranda', 'code' => 'M'],
            ['name' => 'Monagas', 'code' => 'N'],
            ['name' => 'Nueva Esparta', 'code' => 'O'],
            ['name' => 'Portuguesa', 'code' => 'P'],
            ['name' => 'Sucre', 'code' => 'R'],
            ['name' => 'Táchira', 'code' => 'S'],
            ['name' => 'Trujillo', 'code' => 'T'],
            ['name' => 'Dependencias Federales', 'code' => 'W'],
            ['name' => 'Yaracuy', 'code' => 'U'],
            ['name' => 'Zulia', 'code' => 'V'],
        ];
    }
}
