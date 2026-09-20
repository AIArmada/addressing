<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\SouthKorea;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class SouthKoreaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_south_korea_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.south_korea';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'KR';
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
                        areaTypes: ['province', 'metropolitan_city', 'special_city', 'special_self_governing_province', 'special_self_governing_city'],
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
                'metropolitan_city' => ['province'],
                'special_city' => ['province'],
                'special_self_governing_province' => ['province'],
                'special_self_governing_city' => ['province'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'KR', 'is_primary' => true],
                $areaRoles,
            );
        }

        return $roles;
    }

    /** @return array<string, list<array{name: string, name_type?: string, is_preferred?: bool}>> */
    public function areaNames(AddressCountry $country): array
    {
        return [
            'kr:special_self_governing_province:gangwon-state' => [
                ['name' => 'Gangwon', 'name_type' => 'alternative'],
            ],
            'kr:special_self_governing_province:jeonbuk-state' => [
                ['name' => 'North Jeolla', 'name_type' => 'alternative'],
            ],
        ];
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
            __DIR__ . '/../../../resources/geography/south-korea-address-areas.csv',
            self::AREA_SOURCE,
        );
    }

    /**
     * @return array<int, array{area_code: string, source: string, area_level: int, hierarchy_types?: list<string>}>
     */
    public function stateAreaMappings(): array
    {
        /** @var array<int, string> */
        $areaCodes = [
            '11' => '11',
            '26' => '26',
            '27' => '27',
            '28' => '28',
            '29' => '29',
            '30' => '30',
            '31' => '31',
            '41' => '41',
            '42' => '42',
            '43' => '43',
            '44' => '44',
            '45' => '45',
            '46' => '46',
            '47' => '47',
            '48' => '48',
            '49' => '49',
            '50' => '50',
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
            ['name' => 'Seoul', 'code' => '11'],
            ['name' => 'Busan', 'code' => '26'],
            ['name' => 'Daegu', 'code' => '27'],
            ['name' => 'Incheon', 'code' => '28'],
            ['name' => 'Gwangju', 'code' => '29'],
            ['name' => 'Daejeon', 'code' => '30'],
            ['name' => 'Ulsan', 'code' => '31'],
            ['name' => 'Gyeonggi', 'code' => '41'],
            ['name' => 'Gangwon State', 'code' => '42'],
            ['name' => 'North Chungcheong', 'code' => '43'],
            ['name' => 'South Chungcheong', 'code' => '44'],
            ['name' => 'Jeonbuk State', 'code' => '45'],
            ['name' => 'South Jeolla', 'code' => '46'],
            ['name' => 'North Gyeongsang', 'code' => '47'],
            ['name' => 'South Gyeongsang', 'code' => '48'],
            ['name' => 'Jeju', 'code' => '49'],
            ['name' => 'Sejong City', 'code' => '50'],
        ];
    }
}
