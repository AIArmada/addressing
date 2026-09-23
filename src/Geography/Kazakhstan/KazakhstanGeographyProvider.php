<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Kazakhstan;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryAreaTypeLabelProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class KazakhstanGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_kazakhstan_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.kazakhstan';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'KZ';
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
                        label: 'Region / City',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['region', 'city'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'district',
                        label: 'District',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['district'],
                        areaLevels: [2],
                        parentKey: 'region',
                        assignmentRole: 'district',
                    ),
                ],
            ),
        ];
    }

    /** @return array<string, string> */
    public function areaTypeLabels(): array
    {
        // Kazakh administrative terms.
        return [
            'region' => 'Oblys',
            'city' => 'Qala',
            'district' => 'Audan',
        ];
    }

    /** @return list<array{state_code: string, type_labels: array<string, string>}> */
    public function stateAreaTypeLabels(): array
    {
        return [];
    }

    /** @return array<string, list<array{role: string, country_code?: string, is_primary?: bool}>> */
    public function areaRoles(AddressCountry $country): array
    {
        $roles = [];

        foreach ($this->addressAreaSource()->areas() as $area) {
            $areaRoles = match ($area->type) {
                'region' => ['region'],
                'city' => ['city'],
                'district' => ['district'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'KZ', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/kazakhstan-address-areas.csv',
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
            '10' => '10',
            '11' => '11',
            '15' => '15',
            '19' => '19',
            '75' => '75',
            '71' => '71',
            '23' => '23',
            '63' => '63',
            '31' => '31',
            '33' => '33',
            '35' => '35',
            '39' => '39',
            '43' => '43',
            '47' => '47',
            '59' => '59',
            '55' => '55',
            '79' => '79',
            '61' => '61',
            '62' => '62',
            '27' => '27',
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
            ['name' => 'Abai', 'code' => '10'],
            ['name' => 'Akmola', 'code' => '11'],
            ['name' => 'Aktobe', 'code' => '15'],
            ['name' => 'Almaty', 'code' => '19'],
            ['name' => 'Almaty', 'code' => '75'],
            ['name' => 'Astana', 'code' => '71'],
            ['name' => 'Atyrau', 'code' => '23'],
            ['name' => 'East Kazakhstan', 'code' => '63'],
            ['name' => 'Jambyl', 'code' => '31'],
            ['name' => 'Jetisu', 'code' => '33'],
            ['name' => 'Karaganda', 'code' => '35'],
            ['name' => 'Kostanay', 'code' => '39'],
            ['name' => 'Kyzylorda', 'code' => '43'],
            ['name' => 'Mangystau', 'code' => '47'],
            ['name' => 'North Kazakhstan', 'code' => '59'],
            ['name' => 'Pavlodar', 'code' => '55'],
            ['name' => 'Shymkent', 'code' => '79'],
            ['name' => 'Turkistan', 'code' => '61'],
            ['name' => 'Ulytau', 'code' => '62'],
            ['name' => 'West Kazakhstan', 'code' => '27'],
        ];
    }
}
