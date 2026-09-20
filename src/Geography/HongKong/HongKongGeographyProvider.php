<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\HongKong;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class HongKongGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_hong_kong_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.hong_kong';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'HK';
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
                        label: 'District',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['district'],
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
                'district' => ['district'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'HK', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/hong-kong-address-areas.csv',
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
            'HCW' => 'HCW',
            'HEA' => 'HEA',
            'NIS' => 'NIS',
            'KKC' => 'KKC',
            'NKT' => 'NKT',
            'KKT' => 'KKT',
            'NNO' => 'NNO',
            'NSK' => 'NSK',
            'NST' => 'NST',
            'KSS' => 'KSS',
            'HSO' => 'HSO',
            'NTP' => 'NTP',
            'NTW' => 'NTW',
            'NTM' => 'NTM',
            'HWC' => 'HWC',
            'KWT' => 'KWT',
            'KYT' => 'KYT',
            'NYL' => 'NYL',
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
            ['name' => 'Central and Western', 'code' => 'HCW'],
            ['name' => 'Eastern', 'code' => 'HEA'],
            ['name' => 'Islands', 'code' => 'NIS'],
            ['name' => 'Kowloon City', 'code' => 'KKC'],
            ['name' => 'Kwai Tsing', 'code' => 'NKT'],
            ['name' => 'Kwun Tong', 'code' => 'KKT'],
            ['name' => 'North', 'code' => 'NNO'],
            ['name' => 'Sai Kung', 'code' => 'NSK'],
            ['name' => 'Sha Tin', 'code' => 'NST'],
            ['name' => 'Sham Shui Po', 'code' => 'KSS'],
            ['name' => 'Southern', 'code' => 'HSO'],
            ['name' => 'Tai Po', 'code' => 'NTP'],
            ['name' => 'Tsuen Wan', 'code' => 'NTW'],
            ['name' => 'Tuen Mun', 'code' => 'NTM'],
            ['name' => 'Wan Chai', 'code' => 'HWC'],
            ['name' => 'Wong Tai Sin', 'code' => 'KWT'],
            ['name' => 'Yau Tsim Mong', 'code' => 'KYT'],
            ['name' => 'Yuen Long', 'code' => 'NYL'],
        ];
    }
}
