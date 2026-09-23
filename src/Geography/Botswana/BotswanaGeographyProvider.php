<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Botswana;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class BotswanaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_botswana_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.botswana';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'BW';
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
                        key: 'district',
                        label: 'District / City / Town',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['district', 'city', 'town'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'subdistrict',
                        label: 'Subdistrict',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['subdistrict'],
                        areaLevels: [2],
                        parentKey: 'district',
                        assignmentRole: 'subdistrict',
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
                'district' => ['district'],
                'city' => ['city'],
                'town' => ['town'],
                'subdistrict' => ['subdistrict'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'BW', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/botswana-address-areas.csv',
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
            'CE' => 'CE',
            'CH' => 'CH',
            'FR' => 'FR',
            'GA' => 'GA',
            'GH' => 'GH',
            'JW' => 'JW',
            'KG' => 'KG',
            'KL' => 'KL',
            'KW' => 'KW',
            'LO' => 'LO',
            'NE' => 'NE',
            'NW' => 'NW',
            'OR' => 'OR',
            'SP' => 'SP',
            'SE' => 'SE',
            'SO' => 'SO',
            'ST' => 'ST',
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
            ['name' => 'Central', 'code' => 'CE'],
            ['name' => 'Chobe', 'code' => 'CH'],
            ['name' => 'Francistown', 'code' => 'FR'],
            ['name' => 'Gaborone', 'code' => 'GA'],
            ['name' => 'Ghanzi', 'code' => 'GH'],
            ['name' => 'Jwaneng', 'code' => 'JW'],
            ['name' => 'Kgalagadi', 'code' => 'KG'],
            ['name' => 'Kgatleng', 'code' => 'KL'],
            ['name' => 'Kweneng', 'code' => 'KW'],
            ['name' => 'Lobatse', 'code' => 'LO'],
            ['name' => 'North-East', 'code' => 'NE'],
            ['name' => 'North-West', 'code' => 'NW'],
            ['name' => 'Orapa', 'code' => 'OR'],
            ['name' => 'Selibe Phikwe', 'code' => 'SP'],
            ['name' => 'South-East', 'code' => 'SE'],
            ['name' => 'Southern', 'code' => 'SO'],
            ['name' => 'Sowa Town', 'code' => 'ST'],
        ];
    }
}
