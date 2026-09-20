<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\China;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class ChinaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_china_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.china';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'CN';
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
                        label: 'Province / Region / Municipality',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['province', 'autonomous_region', 'municipality', 'special_administrative_region'],
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
                'autonomous_region' => ['province'],
                'municipality' => ['province'],
                'special_administrative_region' => ['province'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'CN', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/china-address-areas.csv',
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
            'AH' => 'AH',
            'BJ' => 'BJ',
            'CQ' => 'CQ',
            'FJ' => 'FJ',
            'GD' => 'GD',
            'GS' => 'GS',
            'GX' => 'GX',
            'GZ' => 'GZ',
            'HA' => 'HA',
            'HB' => 'HB',
            'HE' => 'HE',
            'HI' => 'HI',
            'HK' => 'HK',
            'HL' => 'HL',
            'HN' => 'HN',
            'JL' => 'JL',
            'JS' => 'JS',
            'JX' => 'JX',
            'LN' => 'LN',
            'MO' => 'MO',
            'NM' => 'NM',
            'NX' => 'NX',
            'QH' => 'QH',
            'SC' => 'SC',
            'SD' => 'SD',
            'SH' => 'SH',
            'SN' => 'SN',
            'SX' => 'SX',
            'TJ' => 'TJ',
            'XJ' => 'XJ',
            'XZ' => 'XZ',
            'YN' => 'YN',
            'ZJ' => 'ZJ',
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
            ['name' => 'Anhui', 'code' => 'AH'],
            ['name' => 'Beijing', 'code' => 'BJ'],
            ['name' => 'Chongqing', 'code' => 'CQ'],
            ['name' => 'Fujian', 'code' => 'FJ'],
            ['name' => 'Guangdong', 'code' => 'GD'],
            ['name' => 'Gansu', 'code' => 'GS'],
            ['name' => 'Guangxi', 'code' => 'GX'],
            ['name' => 'Guizhou', 'code' => 'GZ'],
            ['name' => 'Henan', 'code' => 'HA'],
            ['name' => 'Hubei', 'code' => 'HB'],
            ['name' => 'Hebei', 'code' => 'HE'],
            ['name' => 'Hainan', 'code' => 'HI'],
            ['name' => 'Hong Kong', 'code' => 'HK'],
            ['name' => 'Heilongjiang', 'code' => 'HL'],
            ['name' => 'Hunan', 'code' => 'HN'],
            ['name' => 'Jilin', 'code' => 'JL'],
            ['name' => 'Jiangsu', 'code' => 'JS'],
            ['name' => 'Jiangxi', 'code' => 'JX'],
            ['name' => 'Liaoning', 'code' => 'LN'],
            ['name' => 'Macao', 'code' => 'MO'],
            ['name' => 'Inner Mongolia', 'code' => 'NM'],
            ['name' => 'Ningxia', 'code' => 'NX'],
            ['name' => 'Qinghai', 'code' => 'QH'],
            ['name' => 'Sichuan', 'code' => 'SC'],
            ['name' => 'Shandong', 'code' => 'SD'],
            ['name' => 'Shanghai', 'code' => 'SH'],
            ['name' => 'Shaanxi', 'code' => 'SN'],
            ['name' => 'Shanxi', 'code' => 'SX'],
            ['name' => 'Tianjin', 'code' => 'TJ'],
            ['name' => 'Xinjiang', 'code' => 'XJ'],
            ['name' => 'Tibet', 'code' => 'XZ'],
            ['name' => 'Yunnan', 'code' => 'YN'],
            ['name' => 'Zhejiang', 'code' => 'ZJ'],
        ];
    }
}
