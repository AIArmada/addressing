<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Finland;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class FinlandGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_finland_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.finland';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'FI';
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
                static fn (string $role): array => ['role' => $role, 'country_code' => 'FI', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/finland-address-areas.csv',
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
            '08' => '08',
            '07' => '07',
            '19' => '19',
            '05' => '05',
            '09' => '09',
            '10' => '10',
            '13' => '13',
            '14' => '14',
            '15' => '15',
            '12' => '12',
            '16' => '16',
            '11' => '11',
            '17' => '17',
            '02' => '02',
            '03' => '03',
            '04' => '04',
            '06' => '06',
            '18' => '18',
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
            ['name' => 'Central Finland', 'code' => '08'],
            ['name' => 'Central Ostrobothnia', 'code' => '07'],
            ['name' => 'Finland Proper', 'code' => '19'],
            ['name' => 'Kainuu', 'code' => '05'],
            ['name' => 'Kymenlaakso', 'code' => '09'],
            ['name' => 'Lapland', 'code' => '10'],
            ['name' => 'North Karelia', 'code' => '13'],
            ['name' => 'Northern Ostrobothnia', 'code' => '14'],
            ['name' => 'Northern Savonia', 'code' => '15'],
            ['name' => 'Ostrobothnia', 'code' => '12'],
            ['name' => 'Päijänne Tavastia', 'code' => '16'],
            ['name' => 'Pirkanmaa', 'code' => '11'],
            ['name' => 'Satakunta', 'code' => '17'],
            ['name' => 'South Karelia', 'code' => '02'],
            ['name' => 'Southern Ostrobothnia', 'code' => '03'],
            ['name' => 'Southern Savonia', 'code' => '04'],
            ['name' => 'Tavastia Proper', 'code' => '06'],
            ['name' => 'Uusimaa', 'code' => '18'],
        ];
    }
}
