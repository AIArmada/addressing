<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Paraguay;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class ParaguayGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_paraguay_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.paraguay';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'PY';
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
                static fn (string $role): array => ['role' => $role, 'country_code' => 'PY', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/paraguay-address-areas.csv',
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
            '10' => '10',
            '13' => '13',
            'ASU' => 'ASU',
            '19' => '19',
            '5' => '5',
            '6' => '6',
            '14' => '14',
            '11' => '11',
            '1' => '1',
            '3' => '3',
            '4' => '4',
            '7' => '7',
            '8' => '8',
            '12' => '12',
            '9' => '9',
            '15' => '15',
            '2' => '2',
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
            ['name' => 'Alto Paraguay', 'code' => '16'],
            ['name' => 'Alto Paraná', 'code' => '10'],
            ['name' => 'Amambay', 'code' => '13'],
            ['name' => 'Asuncion', 'code' => 'ASU'],
            ['name' => 'Boquerón', 'code' => '19'],
            ['name' => 'Caaguazú', 'code' => '5'],
            ['name' => 'Caazapá', 'code' => '6'],
            ['name' => 'Canindeyú', 'code' => '14'],
            ['name' => 'Central', 'code' => '11'],
            ['name' => 'Concepción', 'code' => '1'],
            ['name' => 'Cordillera', 'code' => '3'],
            ['name' => 'Guairá', 'code' => '4'],
            ['name' => 'Itapúa', 'code' => '7'],
            ['name' => 'Misiones', 'code' => '8'],
            ['name' => 'Ñeembucú', 'code' => '12'],
            ['name' => 'Paraguarí', 'code' => '9'],
            ['name' => 'Presidente Hayes', 'code' => '15'],
            ['name' => 'San Pedro', 'code' => '2'],
        ];
    }
}
