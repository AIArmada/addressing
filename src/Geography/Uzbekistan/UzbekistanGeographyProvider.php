<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Uzbekistan;

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

class UzbekistanGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_uzbekistan_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.uzbekistan';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'UZ';
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
                        label: 'Region / Republic / City',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['region', 'republic', 'city'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'tuman',
                        label: 'Tuman',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['tuman', 'city'],
                        areaLevels: [2],
                        parentKey: 'region',
                        assignmentRole: 'tuman',
                    ),
                ],
            ),
        ];
    }

    /** @return array<string, string> */
    public function areaTypeLabels(): array
    {
        // Cities are shahar; tumans headline correctly.
        return [
            'city' => 'Shahar',
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
            // `city` spans two levels: Tashkent City (L1) vs regional-subordination cities (L2).
            $areaRoles = match (true) {
                $area->type === 'region' || $area->type === 'republic' => ['region'],
                $area->type === 'city' && $area->level === 1 => ['region'],
                $area->type === 'tuman' => ['tuman'],
                $area->type === 'city' => ['tuman'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'UZ', 'is_primary' => true],
                $areaRoles,
            );
        }

        return $roles;
    }

    /** @return array<string, list<array{name: string, name_type?: string, is_preferred?: bool}>> */
    public function areaNames(AddressCountry $country): array
    {
        return [
            'uz:tuman:tashkent-city:shayxontoxur' => [
                ['name' => 'Shayxontohur', 'name_type' => 'alternative'],
            ],
            'uz:tuman:tashkent-city:sirgali' => [
                ['name' => 'Sergeli', 'name_type' => 'alternative'],
            ],
            'uz:tuman:xorazm:hazorasp' => [
                ['name' => 'Xazorasp', 'name_type' => 'alternative'],
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
            __DIR__ . '/../../../resources/geography/uzbekistan-address-areas.csv',
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
            'AN' => 'AN',
            'BU' => 'BU',
            'FA' => 'FA',
            'JI' => 'JI',
            'NG' => 'NG',
            'NW' => 'NW',
            'QA' => 'QA',
            'QR' => 'QR',
            'SA' => 'SA',
            'SI' => 'SI',
            'SU' => 'SU',
            'TK' => 'TK',
            'TO' => 'TO',
            'XO' => 'XO',
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
            ['name' => 'Andijan', 'code' => 'AN'],
            ['name' => 'Bukhara', 'code' => 'BU'],
            ['name' => 'Fergana', 'code' => 'FA'],
            ['name' => 'Jizzakh', 'code' => 'JI'],
            ['name' => 'Namangan', 'code' => 'NG'],
            ['name' => 'Navoiy', 'code' => 'NW'],
            ['name' => 'Qashqadaryo', 'code' => 'QA'],
            ['name' => 'Karakalpakstan', 'code' => 'QR'],
            ['name' => 'Samarqand', 'code' => 'SA'],
            ['name' => 'Sirdaryo', 'code' => 'SI'],
            ['name' => 'Surxondaryo', 'code' => 'SU'],
            ['name' => 'Tashkent City', 'code' => 'TK'],
            ['name' => 'Tashkent Region', 'code' => 'TO'],
            ['name' => 'Xorazm', 'code' => 'XO'],
        ];
    }
}
