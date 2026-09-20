<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\MarshallIslands;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class MarshallIslandsGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_marshall_islands_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.marshall_islands';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'MH';
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
                        key: 'municipality',
                        label: 'Municipality / Chain',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['municipality', 'chain'],
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
                'municipality' => ['municipality'],
                'chain' => ['chain'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'MH', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/marshall-islands-address-areas.csv',
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
            'ALL' => 'ALL',
            'ALK' => 'ALK',
            'ARN' => 'ARN',
            'AUR' => 'AUR',
            'KIL' => 'KIL',
            'EBO' => 'EBO',
            'ENI' => 'ENI',
            'JAB' => 'JAB',
            'JAL' => 'JAL',
            'KWA' => 'KWA',
            'LAE' => 'LAE',
            'LIB' => 'LIB',
            'LIK' => 'LIK',
            'MAJ' => 'MAJ',
            'MAL' => 'MAL',
            'MEJ' => 'MEJ',
            'MIL' => 'MIL',
            'NMK' => 'NMK',
            'NMU' => 'NMU',
            'L' => 'L',
            'T' => 'T',
            'RON' => 'RON',
            'UJA' => 'UJA',
            'UTI' => 'UTI',
            'WTH' => 'WTH',
            'WTJ' => 'WTJ',
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
            ['name' => 'Ailinglaplap', 'code' => 'ALL'],
            ['name' => 'Ailuk', 'code' => 'ALK'],
            ['name' => 'Arno', 'code' => 'ARN'],
            ['name' => 'Aur', 'code' => 'AUR'],
            ['name' => 'Bikini & Kili', 'code' => 'KIL'],
            ['name' => 'Ebon', 'code' => 'EBO'],
            ['name' => 'Enewetak & Ujelang', 'code' => 'ENI'],
            ['name' => 'Jabat', 'code' => 'JAB'],
            ['name' => 'Jaluit', 'code' => 'JAL'],
            ['name' => 'Kwajalein', 'code' => 'KWA'],
            ['name' => 'Lae', 'code' => 'LAE'],
            ['name' => 'Lib', 'code' => 'LIB'],
            ['name' => 'Likiep', 'code' => 'LIK'],
            ['name' => 'Majuro', 'code' => 'MAJ'],
            ['name' => 'Maloelap', 'code' => 'MAL'],
            ['name' => 'Mejit', 'code' => 'MEJ'],
            ['name' => 'Mili', 'code' => 'MIL'],
            ['name' => 'Namdrik', 'code' => 'NMK'],
            ['name' => 'Namu', 'code' => 'NMU'],
            ['name' => 'Ralik', 'code' => 'L'],
            ['name' => 'Ratak', 'code' => 'T'],
            ['name' => 'Rongelap', 'code' => 'RON'],
            ['name' => 'Ujae', 'code' => 'UJA'],
            ['name' => 'Utrik', 'code' => 'UTI'],
            ['name' => 'Wotho', 'code' => 'WTH'],
            ['name' => 'Wotje', 'code' => 'WTJ'],
        ];
    }
}
