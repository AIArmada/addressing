<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\CentralAfricanRepublic;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class CentralAfricanRepublicGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
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
                        label: 'Prefecture / Commune / Economic Prefecture',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['prefecture', 'commune', 'economic_prefecture'],
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
                'prefecture' => ['prefecture'],
                'commune' => ['commune'],
                'economic_prefecture' => ['economic_prefecture'],
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
            'LB' => 'LB',
            'HS' => 'HS',
            'MB' => 'MB',
            'KB' => 'KB',
            'NM' => 'NM',
            'MP' => 'MP',
            'UK' => 'UK',
            'AC' => 'AC',
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
            ['name' => 'Lobaye', 'code' => 'LB'],
            ['name' => 'Mambéré-Kadéï', 'code' => 'HS'],
            ['name' => 'Mbomou', 'code' => 'MB'],
            ['name' => 'Nana-Grébizi', 'code' => 'KB'],
            ['name' => 'Nana-Mambéré', 'code' => 'NM'],
            ['name' => 'Ombella-M\'Poko', 'code' => 'MP'],
            ['name' => 'Ouaka', 'code' => 'UK'],
            ['name' => 'Ouham', 'code' => 'AC'],
            ['name' => 'Ouham-Pendé', 'code' => 'OP'],
            ['name' => 'Sangha-Mbaéré', 'code' => 'SE'],
            ['name' => 'Vakaga', 'code' => 'VK'],
        ];
    }
}
