<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\CentralAfricanRepublic;

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

class CentralAfricanRepublicGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_central_african_republic_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.central_african_republic';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'CF';
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
                        label: 'Prefecture / Economic Prefecture',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['prefecture', 'economic_prefecture'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'subprefecture',
                        label: 'Subprefecture',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['subprefecture'],
                        areaLevels: [2],
                        parentKey: 'prefecture',
                        assignmentRole: 'subprefecture',
                    ),
                ],
            ),
        ];
    }

    /** @return array<string, string> */
    public function areaTypeLabels(): array
    {
        // French administrative terms.
        return [
            'prefecture' => 'Préfecture',
            'economic_prefecture' => 'Préfecture Économique',
            'subprefecture' => 'Sous-préfecture',
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
                'prefecture' => ['prefecture'],
                'economic_prefecture' => ['economic_prefecture'],
                'subprefecture' => ['subprefecture'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'CF', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/central-african-republic-address-areas.csv',
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
            'BB' => 'BB',
            'BGF' => 'BGF',
            'BK' => 'BK',
            'HM' => 'HM',
            'HK' => 'HK',
            'KG' => 'KG',
            'LP' => 'LP',
            'LB' => 'LB',
            'ME' => 'ME',
            'HS' => 'HS',
            'MB' => 'MB',
            'KB' => 'KB',
            'NM' => 'NM',
            'MP' => 'MP',
            'UK' => 'UK',
            'AC' => 'AC',
            'OF' => 'OF',
            'OP' => 'OP',
            'SE' => 'SE',
            'VK' => 'VK',
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
            ['name' => 'Bamingui-Bangoran', 'code' => 'BB'],
            ['name' => 'Bangui', 'code' => 'BGF'],
            ['name' => 'Basse-Kotto', 'code' => 'BK'],
            ['name' => 'Haut-Mbomou', 'code' => 'HM'],
            ['name' => 'Haute-Kotto', 'code' => 'HK'],
            ['name' => 'Kémo', 'code' => 'KG'],
            ['name' => 'Lim-Pendé', 'code' => 'LP'],
            ['name' => 'Lobaye', 'code' => 'LB'],
            ['name' => 'Mambéré', 'code' => 'ME'],
            ['name' => 'Mambéré-Kadéï', 'code' => 'HS'],
            ['name' => 'Mbomou', 'code' => 'MB'],
            ['name' => 'Nana-Grébizi', 'code' => 'KB'],
            ['name' => 'Nana-Mambéré', 'code' => 'NM'],
            ['name' => 'Ombella-M\'Poko', 'code' => 'MP'],
            ['name' => 'Ouaka', 'code' => 'UK'],
            ['name' => 'Ouham', 'code' => 'AC'],
            ['name' => 'Ouham-Fafa', 'code' => 'OF'],
            ['name' => 'Ouham-Pendé', 'code' => 'OP'],
            ['name' => 'Sangha-Mbaéré', 'code' => 'SE'],
            ['name' => 'Vakaga', 'code' => 'VK'],
        ];
    }
}
