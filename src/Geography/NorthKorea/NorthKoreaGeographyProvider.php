<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\NorthKorea;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class NorthKoreaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_north_korea_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.north_korea';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'KP';
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
                        label: 'Province / City',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['province', 'metropolitan_city', 'capital_city', 'special_city'],
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
                'metropolitan_city' => ['metropolitan_city'],
                'capital_city' => ['capital_city'],
                'special_city' => ['special_city'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'KP', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/north-korea-address-areas.csv',
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
            '04' => '04',
            '15' => '15',
            '07' => '07',
            '14' => '14',
            '09' => '09',
            '06' => '06',
            '03' => '03',
            '01' => '01',
            '13' => '13',
            '10' => '10',
            '08' => '08',
            '05' => '05',
            '02' => '02',
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
            ['name' => 'Chagang', 'code' => '04'],
            ['name' => 'Kaesong', 'code' => '15'],
            ['name' => 'Kangwon', 'code' => '07'],
            ['name' => 'Nampho', 'code' => '14'],
            ['name' => 'North Hamgyong', 'code' => '09'],
            ['name' => 'North Hwanghae', 'code' => '06'],
            ['name' => 'North Pyongan', 'code' => '03'],
            ['name' => 'Pyongyang', 'code' => '01'],
            ['name' => 'Rason', 'code' => '13'],
            ['name' => 'Ryanggang', 'code' => '10'],
            ['name' => 'South Hamgyong', 'code' => '08'],
            ['name' => 'South Hwanghae', 'code' => '05'],
            ['name' => 'South Pyongan', 'code' => '02'],
        ];
    }
}
