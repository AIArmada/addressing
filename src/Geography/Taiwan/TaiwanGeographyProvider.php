<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Taiwan;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class TaiwanGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_taiwan_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.taiwan';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'TW';
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
                        key: 'division',
                        label: 'Municipality / County / City',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['special_municipality', 'county', 'city'],
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
                'special_municipality' => ['division'],
                'county' => ['division'],
                'city' => ['division'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'TW', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/taiwan-address-areas.csv',
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
            'CHA' => 'CHA',
            'CYI' => 'CYI',
            'CYQ' => 'CYQ',
            'HSQ' => 'HSQ',
            'HSZ' => 'HSZ',
            'HUA' => 'HUA',
            'ILA' => 'ILA',
            'KEE' => 'KEE',
            'KHH' => 'KHH',
            'KIN' => 'KIN',
            'LIE' => 'LIE',
            'MIA' => 'MIA',
            'NAN' => 'NAN',
            'NWT' => 'NWT',
            'PEN' => 'PEN',
            'PIF' => 'PIF',
            'TAO' => 'TAO',
            'TNN' => 'TNN',
            'TPE' => 'TPE',
            'TTT' => 'TTT',
            'TXG' => 'TXG',
            'YUN' => 'YUN',
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
            ['name' => 'Changhua', 'code' => 'CHA'],
            ['name' => 'Chiayi', 'code' => 'CYI'],
            ['name' => 'Chiayi County', 'code' => 'CYQ'],
            ['name' => 'Hsinchu County', 'code' => 'HSQ'],
            ['name' => 'Hsinchu', 'code' => 'HSZ'],
            ['name' => 'Hualien', 'code' => 'HUA'],
            ['name' => 'Yilan', 'code' => 'ILA'],
            ['name' => 'Keelung', 'code' => 'KEE'],
            ['name' => 'Kaohsiung', 'code' => 'KHH'],
            ['name' => 'Kinmen', 'code' => 'KIN'],
            ['name' => 'Lienchiang', 'code' => 'LIE'],
            ['name' => 'Miaoli', 'code' => 'MIA'],
            ['name' => 'Nantou', 'code' => 'NAN'],
            ['name' => 'New Taipei', 'code' => 'NWT'],
            ['name' => 'Penghu', 'code' => 'PEN'],
            ['name' => 'Pingtung', 'code' => 'PIF'],
            ['name' => 'Taoyuan', 'code' => 'TAO'],
            ['name' => 'Tainan', 'code' => 'TNN'],
            ['name' => 'Taipei', 'code' => 'TPE'],
            ['name' => 'Taitung', 'code' => 'TTT'],
            ['name' => 'Taichung', 'code' => 'TXG'],
            ['name' => 'Yunlin', 'code' => 'YUN'],
        ];
    }
}
