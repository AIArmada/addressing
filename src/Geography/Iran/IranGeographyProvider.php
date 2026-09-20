<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Iran;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class IranGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_iran_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.iran';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'IR';
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
                        label: 'Province',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['province'],
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
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'IR', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/iran-address-areas.csv',
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
            '30' => '30',
            '24' => '24',
            '18' => '18',
            '14' => '14',
            '03' => '03',
            '07' => '07',
            '01' => '01',
            '27' => '27',
            '13' => '13',
            '22' => '22',
            '16' => '16',
            '10' => '10',
            '08' => '08',
            '05' => '05',
            '06' => '06',
            '17' => '17',
            '12' => '12',
            '15' => '15',
            '00' => '00',
            '02' => '02',
            '28' => '28',
            '26' => '26',
            '25' => '25',
            '09' => '09',
            '20' => '20',
            '11' => '11',
            '29' => '29',
            '23' => '23',
            '04' => '04',
            '21' => '21',
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
            ['name' => 'Alborz', 'code' => '30'],
            ['name' => 'Ardabil', 'code' => '24'],
            ['name' => 'Bushehr', 'code' => '18'],
            ['name' => 'Chaharmahal and Bakhtiari', 'code' => '14'],
            ['name' => 'East Azerbaijan', 'code' => '03'],
            ['name' => 'Fars', 'code' => '07'],
            ['name' => 'Gilan', 'code' => '01'],
            ['name' => 'Golestan', 'code' => '27'],
            ['name' => 'Hamadan', 'code' => '13'],
            ['name' => 'Hormozgan', 'code' => '22'],
            ['name' => 'Ilam', 'code' => '16'],
            ['name' => 'Isfahan', 'code' => '10'],
            ['name' => 'Kerman', 'code' => '08'],
            ['name' => 'Kermanshah', 'code' => '05'],
            ['name' => 'Khuzestan', 'code' => '06'],
            ['name' => 'Kohgiluyeh and Boyer-Ahmad', 'code' => '17'],
            ['name' => 'Kurdistan', 'code' => '12'],
            ['name' => 'Lorestan', 'code' => '15'],
            ['name' => 'Markazi', 'code' => '00'],
            ['name' => 'Mazandaran', 'code' => '02'],
            ['name' => 'North Khorasan', 'code' => '28'],
            ['name' => 'Qazvin', 'code' => '26'],
            ['name' => 'Qom', 'code' => '25'],
            ['name' => 'Razavi Khorasan', 'code' => '09'],
            ['name' => 'Semnan', 'code' => '20'],
            ['name' => 'Sistan and Baluchestan', 'code' => '11'],
            ['name' => 'South Khorasan', 'code' => '29'],
            ['name' => 'Tehran', 'code' => '23'],
            ['name' => 'West Azarbaijan', 'code' => '04'],
            ['name' => 'Yazd', 'code' => '21'],
            ['name' => 'Zanjan', 'code' => '19'],
        ];
    }
}
