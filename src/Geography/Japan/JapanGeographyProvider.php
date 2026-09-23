<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Japan;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class JapanGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_japan_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.japan';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'JP';
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
                        key: 'prefecture',
                        label: 'Prefecture',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['prefecture'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'municipality',
                        label: 'Municipality',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['city', 'town', 'village', 'ward'],
                        areaLevels: [2],
                        parentKey: 'prefecture',
                        assignmentRole: 'municipality',
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
                'prefecture' => ['prefecture'],
                'city', 'town', 'village', 'ward' => ['municipality'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'JP', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/japan-address-areas.csv',
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
            '01' => '01',
            '02' => '02',
            '03' => '03',
            '04' => '04',
            '05' => '05',
            '06' => '06',
            '07' => '07',
            '08' => '08',
            '09' => '09',
            '10' => '10',
            '11' => '11',
            '12' => '12',
            '13' => '13',
            '14' => '14',
            '15' => '15',
            '16' => '16',
            '17' => '17',
            '18' => '18',
            '19' => '19',
            '20' => '20',
            '21' => '21',
            '22' => '22',
            '23' => '23',
            '24' => '24',
            '25' => '25',
            '26' => '26',
            '27' => '27',
            '28' => '28',
            '29' => '29',
            '30' => '30',
            '31' => '31',
            '32' => '32',
            '33' => '33',
            '34' => '34',
            '35' => '35',
            '36' => '36',
            '37' => '37',
            '38' => '38',
            '39' => '39',
            '40' => '40',
            '41' => '41',
            '42' => '42',
            '43' => '43',
            '44' => '44',
            '45' => '45',
            '46' => '46',
            '47' => '47',
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
            ['name' => 'Hokkaido', 'code' => '01'],
            ['name' => 'Aomori', 'code' => '02'],
            ['name' => 'Iwate', 'code' => '03'],
            ['name' => 'Miyagi', 'code' => '04'],
            ['name' => 'Akita', 'code' => '05'],
            ['name' => 'Yamagata', 'code' => '06'],
            ['name' => 'Fukushima', 'code' => '07'],
            ['name' => 'Ibaraki', 'code' => '08'],
            ['name' => 'Tochigi', 'code' => '09'],
            ['name' => 'Gunma', 'code' => '10'],
            ['name' => 'Saitama', 'code' => '11'],
            ['name' => 'Chiba', 'code' => '12'],
            ['name' => 'Tokyo', 'code' => '13'],
            ['name' => 'Kanagawa', 'code' => '14'],
            ['name' => 'Niigata', 'code' => '15'],
            ['name' => 'Toyama', 'code' => '16'],
            ['name' => 'Ishikawa', 'code' => '17'],
            ['name' => 'Fukui', 'code' => '18'],
            ['name' => 'Yamanashi', 'code' => '19'],
            ['name' => 'Nagano', 'code' => '20'],
            ['name' => 'Gifu', 'code' => '21'],
            ['name' => 'Shizuoka', 'code' => '22'],
            ['name' => 'Aichi', 'code' => '23'],
            ['name' => 'Mie', 'code' => '24'],
            ['name' => 'Shiga', 'code' => '25'],
            ['name' => 'Kyoto', 'code' => '26'],
            ['name' => 'Osaka', 'code' => '27'],
            ['name' => 'Hyogo', 'code' => '28'],
            ['name' => 'Nara', 'code' => '29'],
            ['name' => 'Wakayama', 'code' => '30'],
            ['name' => 'Tottori', 'code' => '31'],
            ['name' => 'Shimane', 'code' => '32'],
            ['name' => 'Okayama', 'code' => '33'],
            ['name' => 'Hiroshima', 'code' => '34'],
            ['name' => 'Yamaguchi', 'code' => '35'],
            ['name' => 'Tokushima', 'code' => '36'],
            ['name' => 'Kagawa', 'code' => '37'],
            ['name' => 'Ehime', 'code' => '38'],
            ['name' => 'Kochi', 'code' => '39'],
            ['name' => 'Fukuoka', 'code' => '40'],
            ['name' => 'Saga', 'code' => '41'],
            ['name' => 'Nagasaki', 'code' => '42'],
            ['name' => 'Kumamoto', 'code' => '43'],
            ['name' => 'Oita', 'code' => '44'],
            ['name' => 'Miyazaki', 'code' => '45'],
            ['name' => 'Kagoshima', 'code' => '46'],
            ['name' => 'Okinawa', 'code' => '47'],
        ];
    }
}
