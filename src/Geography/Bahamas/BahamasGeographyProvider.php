<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Bahamas;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class BahamasGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_bahamas_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.bahamas';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'BS';
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
                        label: 'District / Island',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['district', 'island'],
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
                'island' => ['island'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'BS', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/bahamas-address-areas.csv',
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
            'AK' => 'AK',
            'BY' => 'BY',
            'BI' => 'BI',
            'BP' => 'BP',
            'CI' => 'CI',
            'CO' => 'CO',
            'CS' => 'CS',
            'CE' => 'CE',
            'CK' => 'CK',
            'EG' => 'EG',
            'EX' => 'EX',
            'FP' => 'FP',
            'GC' => 'GC',
            'HI' => 'HI',
            'HT' => 'HT',
            'IN' => 'IN',
            'LI' => 'LI',
            'MC' => 'MC',
            'MG' => 'MG',
            'MI' => 'MI',
            'NP' => 'NP',
            'NO' => 'NO',
            'NS' => 'NS',
            'NE' => 'NE',
            'RI' => 'RI',
            'RC' => 'RC',
            'SS' => 'SS',
            'SO' => 'SO',
            'SA' => 'SA',
            'SE' => 'SE',
            'SW' => 'SW',
            'WG' => 'WG',
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
            ['name' => 'Acklins', 'code' => 'AK'],
            ['name' => 'Berry Islands', 'code' => 'BY'],
            ['name' => 'Bimini', 'code' => 'BI'],
            ['name' => 'Black Point', 'code' => 'BP'],
            ['name' => 'Cat Island', 'code' => 'CI'],
            ['name' => 'Central Abaco', 'code' => 'CO'],
            ['name' => 'Central Andros', 'code' => 'CS'],
            ['name' => 'Central Eleuthera', 'code' => 'CE'],
            ['name' => 'Crooked Island', 'code' => 'CK'],
            ['name' => 'East Grand Bahama', 'code' => 'EG'],
            ['name' => 'Exuma', 'code' => 'EX'],
            ['name' => 'Freeport', 'code' => 'FP'],
            ['name' => 'Grand Cay', 'code' => 'GC'],
            ['name' => 'Harbour Island', 'code' => 'HI'],
            ['name' => 'Hope Town', 'code' => 'HT'],
            ['name' => 'Inagua', 'code' => 'IN'],
            ['name' => 'Long Island', 'code' => 'LI'],
            ['name' => 'Mangrove Cay', 'code' => 'MC'],
            ['name' => 'Mayaguana', 'code' => 'MG'],
            ['name' => 'Moore\'s Island', 'code' => 'MI'],
            ['name' => 'New Providence', 'code' => 'NP'],
            ['name' => 'North Abaco', 'code' => 'NO'],
            ['name' => 'North Andros', 'code' => 'NS'],
            ['name' => 'North Eleuthera', 'code' => 'NE'],
            ['name' => 'Ragged Island', 'code' => 'RI'],
            ['name' => 'Rum Cay', 'code' => 'RC'],
            ['name' => 'San Salvador Island', 'code' => 'SS'],
            ['name' => 'South Abaco', 'code' => 'SO'],
            ['name' => 'South Andros', 'code' => 'SA'],
            ['name' => 'South Eleuthera', 'code' => 'SE'],
            ['name' => 'Spanish Wells', 'code' => 'SW'],
            ['name' => 'West Grand Bahama', 'code' => 'WG'],
        ];
    }
}
