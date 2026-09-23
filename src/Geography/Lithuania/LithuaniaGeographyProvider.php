<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Lithuania;

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

class LithuaniaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_lithuania_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.lithuania';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'LT';
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
                        key: 'county',
                        label: 'County',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['county'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'municipality',
                        label: 'District Municipality / Municipality / City Municipality',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['district_municipality', 'municipality', 'city_municipality'],
                        areaLevels: [2],
                        parentKey: 'county',
                        assignmentRole: 'municipality',
                    ),
                ],
            ),
        ];
    }

    /** @return array<string, string> */
    public function areaTypeLabels(): array
    {
        // Lithuanian administrative terms.
        return [
            'county' => 'Apskritis',
            'district_municipality' => 'Rajono Savivaldybė',
            'city_municipality' => 'Miesto Savivaldybė',
            'municipality' => 'Savivaldybė',
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
                'county' => ['county'],
                'district_municipality' => ['district_municipality'],
                'municipality' => ['municipality'],
                'city_municipality' => ['city_municipality'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'LT', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/lithuania-address-areas.csv',
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
            'AL' => 'AL',
            'KU' => 'KU',
            'KL' => 'KL',
            'MR' => 'MR',
            'PN' => 'PN',
            'SA' => 'SA',
            'TA' => 'TA',
            'TE' => 'TE',
            'UT' => 'UT',
            'VL' => 'VL',
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
            ['name' => 'Alytus', 'code' => 'AL'],
            ['name' => 'Kaunas', 'code' => 'KU'],
            ['name' => 'Klaipėda', 'code' => 'KL'],
            ['name' => 'Marijampolė', 'code' => 'MR'],
            ['name' => 'Panevėžys', 'code' => 'PN'],
            ['name' => 'Šiauliai', 'code' => 'SA'],
            ['name' => 'Tauragė', 'code' => 'TA'],
            ['name' => 'Telšiai', 'code' => 'TE'],
            ['name' => 'Utena', 'code' => 'UT'],
            ['name' => 'Vilnius', 'code' => 'VL'],
        ];
    }
}
