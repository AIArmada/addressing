<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Cambodia;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class CambodiaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_cambodia_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.cambodia';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'KH';
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
                        label: 'Province / Municipality',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['province', 'municipality'],
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
                'municipality' => ['province'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'KH', 'is_primary' => true],
                $areaRoles,
            );
        }

        return $roles;
    }

    /** @return array<string, list<array{name: string, name_type?: string, is_preferred?: bool}>> */
    public function areaNames(AddressCountry $country): array
    {
        return [
            'kh:province:preah-sihanouk' => [
                ['name' => 'Sihanoukville', 'name_type' => 'alternative'],
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
            __DIR__ . '/../../../resources/geography/cambodia-address-areas.csv',
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
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5',
            '6' => '6',
            '7' => '7',
            '8' => '8',
            '9' => '9',
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
            ['name' => 'Banteay Meanchey', 'code' => '1'],
            ['name' => 'Battambang', 'code' => '2'],
            ['name' => 'Kampong Cham', 'code' => '3'],
            ['name' => 'Kampong Chhnang', 'code' => '4'],
            ['name' => 'Kampong Speu', 'code' => '5'],
            ['name' => 'Kampong Thom', 'code' => '6'],
            ['name' => 'Kampot', 'code' => '7'],
            ['name' => 'Kandal', 'code' => '8'],
            ['name' => 'Koh Kong', 'code' => '9'],
            ['name' => 'Kratie', 'code' => '10'],
            ['name' => 'Mondulkiri', 'code' => '11'],
            ['name' => 'Phnom Penh', 'code' => '12'],
            ['name' => 'Preah Vihear', 'code' => '13'],
            ['name' => 'Prey Veng', 'code' => '14'],
            ['name' => 'Pursat', 'code' => '15'],
            ['name' => 'Ratanakiri', 'code' => '16'],
            ['name' => 'Siem Reap', 'code' => '17'],
            ['name' => 'Preah Sihanouk', 'code' => '18'],
            ['name' => 'Stung Treng', 'code' => '19'],
            ['name' => 'Svay Rieng', 'code' => '20'],
            ['name' => 'Takeo', 'code' => '21'],
            ['name' => 'Oddar Meanchey', 'code' => '22'],
            ['name' => 'Kep', 'code' => '23'],
            ['name' => 'Pailin', 'code' => '24'],
            ['name' => 'Tboung Khmum', 'code' => '25'],
        ];
    }
}
