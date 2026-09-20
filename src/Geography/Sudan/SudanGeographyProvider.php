<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Sudan;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class SudanGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_sudan_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.sudan';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'SD';
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
                        key: 'state',
                        label: 'State',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['state'],
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
                'state' => ['state'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'SD', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/sudan-address-areas.csv',
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
            'DC' => 'DC',
            'DE' => 'DE',
            'DN' => 'DN',
            'DS' => 'DS',
            'DW' => 'DW',
            'GD' => 'GD',
            'GK' => 'GK',
            'GZ' => 'GZ',
            'KA' => 'KA',
            'KH' => 'KH',
            'KN' => 'KN',
            'KS' => 'KS',
            'NB' => 'NB',
            'NO' => 'NO',
            'NR' => 'NR',
            'NW' => 'NW',
            'RS' => 'RS',
            'SI' => 'SI',
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
            ['name' => 'Central Darfur', 'code' => 'DC'],
            ['name' => 'East Darfur', 'code' => 'DE'],
            ['name' => 'North Darfur', 'code' => 'DN'],
            ['name' => 'South Darfur', 'code' => 'DS'],
            ['name' => 'West Darfur', 'code' => 'DW'],
            ['name' => 'Al Qadarif', 'code' => 'GD'],
            ['name' => 'West Kordofan', 'code' => 'GK'],
            ['name' => 'Al Jazirah', 'code' => 'GZ'],
            ['name' => 'Kassala', 'code' => 'KA'],
            ['name' => 'Khartoum', 'code' => 'KH'],
            ['name' => 'North Kordofan', 'code' => 'KN'],
            ['name' => 'South Kordofan', 'code' => 'KS'],
            ['name' => 'Blue Nile', 'code' => 'NB'],
            ['name' => 'Northern', 'code' => 'NO'],
            ['name' => 'River Nile', 'code' => 'NR'],
            ['name' => 'White Nile', 'code' => 'NW'],
            ['name' => 'Red Sea', 'code' => 'RS'],
            ['name' => 'Sennar', 'code' => 'SI'],
        ];
    }
}
