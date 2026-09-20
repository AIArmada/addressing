<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Tunisia;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class TunisiaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_tunisia_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.tunisia';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'TN';
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
                        key: 'governorate',
                        label: 'Governorate',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['governorate'],
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
                'governorate' => ['governorate'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'TN', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/tunisia-address-areas.csv',
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
            '12' => '12',
            '31' => '31',
            '13' => '13',
            '23' => '23',
            '81' => '81',
            '71' => '71',
            '32' => '32',
            '41' => '41',
            '42' => '42',
            '73' => '73',
            '33' => '33',
            '53' => '53',
            '14' => '14',
            '82' => '82',
            '52' => '52',
            '21' => '21',
            '61' => '61',
            '43' => '43',
            '34' => '34',
            '51' => '51',
            '83' => '83',
            '72' => '72',
            '11' => '11',
            '22' => '22',
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
            ['name' => 'Ariana', 'code' => '12'],
            ['name' => 'Béja', 'code' => '31'],
            ['name' => 'Ben Arous', 'code' => '13'],
            ['name' => 'Bizerte', 'code' => '23'],
            ['name' => 'Gabès', 'code' => '81'],
            ['name' => 'Gafsa', 'code' => '71'],
            ['name' => 'Jendouba', 'code' => '32'],
            ['name' => 'Kairouan', 'code' => '41'],
            ['name' => 'Kasserine', 'code' => '42'],
            ['name' => 'Kebili', 'code' => '73'],
            ['name' => 'Kef', 'code' => '33'],
            ['name' => 'Mahdia', 'code' => '53'],
            ['name' => 'Manouba', 'code' => '14'],
            ['name' => 'Medenine', 'code' => '82'],
            ['name' => 'Monastir', 'code' => '52'],
            ['name' => 'Nabeul', 'code' => '21'],
            ['name' => 'Sfax', 'code' => '61'],
            ['name' => 'Sidi Bouzid', 'code' => '43'],
            ['name' => 'Siliana', 'code' => '34'],
            ['name' => 'Sousse', 'code' => '51'],
            ['name' => 'Tataouine', 'code' => '83'],
            ['name' => 'Tozeur', 'code' => '72'],
            ['name' => 'Tunis', 'code' => '11'],
            ['name' => 'Zaghouan', 'code' => '22'],
        ];
    }
}
