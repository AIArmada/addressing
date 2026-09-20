<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Cuba;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class CubaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_cuba_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.cuba';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'CU';
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
                        label: 'Province / Special Municipality',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['province', 'special_municipality'],
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
                'special_municipality' => ['special_municipality'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'CU', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/cuba-address-areas.csv',
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
            '15' => '15',
            '09' => '09',
            '08' => '08',
            '06' => '06',
            '12' => '12',
            '14' => '14',
            '03' => '03',
            '11' => '11',
            '99' => '99',
            '10' => '10',
            '04' => '04',
            '16' => '16',
            '01' => '01',
            '07' => '07',
            '13' => '13',
            '05' => '05',
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
            ['name' => 'Artemisa', 'code' => '15'],
            ['name' => 'Camagüey', 'code' => '09'],
            ['name' => 'Ciego de Ávila', 'code' => '08'],
            ['name' => 'Cienfuegos', 'code' => '06'],
            ['name' => 'Granma', 'code' => '12'],
            ['name' => 'Guantánamo', 'code' => '14'],
            ['name' => 'Havana', 'code' => '03'],
            ['name' => 'Holguín', 'code' => '11'],
            ['name' => 'Isla de la Juventud', 'code' => '99'],
            ['name' => 'Las Tunas', 'code' => '10'],
            ['name' => 'Matanzas', 'code' => '04'],
            ['name' => 'Mayabeque', 'code' => '16'],
            ['name' => 'Pinar del Río', 'code' => '01'],
            ['name' => 'Sancti Spíritus', 'code' => '07'],
            ['name' => 'Santiago de Cuba', 'code' => '13'],
            ['name' => 'Villa Clara', 'code' => '05'],
        ];
    }
}
