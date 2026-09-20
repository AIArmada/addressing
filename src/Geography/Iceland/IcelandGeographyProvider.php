<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Iceland;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class IcelandGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_iceland_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.iceland';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'IS';
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
                        label: 'Region / Municipality',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['region', 'municipality'],
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
                'region' => ['region'],
                'municipality' => ['municipality'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'IS', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/iceland-address-areas.csv',
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
            'AKN' => 'AKN',
            'AKU' => 'AKU',
            'SFA' => 'SFA',
            'ARN' => 'ARN',
            'ASA' => 'ASA',
            'BLA' => 'BLA',
            'BOL' => 'BOL',
            'BOG' => 'BOG',
            '1' => '1',
            'DAB' => 'DAB',
            'DAV' => 'DAV',
            '7' => '7',
            'EOM' => 'EOM',
            'EYF' => 'EYF',
            'FJL' => 'FJL',
            'FJD' => 'FJD',
            'FLR' => 'FLR',
            'FLA' => 'FLA',
            'GAR' => 'GAR',
            'GOG' => 'GOG',
            'GRN' => 'GRN',
            'GRU' => 'GRU',
            'GRY' => 'GRY',
            'HAF' => 'HAF',
            'HRG' => 'HRG',
            'SHF' => 'SHF',
            'HRU' => 'HRU',
            'HUG' => 'HUG',
            'HUV' => 'HUV',
            'HVA' => 'HVA',
            'HVE' => 'HVE',
            'ISA' => 'ISA',
            'KAL' => 'KAL',
            'KJO' => 'KJO',
            'KOP' => 'KOP',
            'LAN' => 'LAN',
            'MOS' => 'MOS',
            'MUL' => 'MUL',
            'MYR' => 'MYR',
            'NOR' => 'NOR',
            '6' => '6',
            '5' => '5',
            'SOL' => 'SOL',
            'RGE' => 'RGE',
            'RGY' => 'RGY',
            'RHH' => 'RHH',
            'RKN' => 'RKN',
            'RKV' => 'RKV',
            'SEL' => 'SEL',
            'SKF' => 'SKF',
            'SKG' => 'SKG',
            'SKR' => 'SKR',
            'SSS' => 'SSS',
            'SOG' => 'SOG',
            'SKO' => 'SKO',
            'SNF' => 'SNF',
            '8' => '8',
            '2' => '2',
            'STR' => 'STR',
            'STY' => 'STY',
            'SDV' => 'SDV',
            'SDN' => 'SDN',
            'SBT' => 'SBT',
            'TAL' => 'TAL',
            'TJO' => 'TJO',
            'VEM' => 'VEM',
            'VER' => 'VER',
            'SVG' => 'SVG',
            'VOP' => 'VOP',
            '3' => '3',
            '4' => '4',
            'THG' => 'THG',
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
            ['name' => 'Akranes', 'code' => 'AKN'],
            ['name' => 'Akureyri', 'code' => 'AKU'],
            ['name' => 'Árborg', 'code' => 'SFA'],
            ['name' => 'Árneshreppur', 'code' => 'ARN'],
            ['name' => 'Ásahreppur', 'code' => 'ASA'],
            ['name' => 'Bláskógabyggð', 'code' => 'BLA'],
            ['name' => 'Bolungarvík', 'code' => 'BOL'],
            ['name' => 'Borgarbyggð', 'code' => 'BOG'],
            ['name' => 'Capital', 'code' => '1'],
            ['name' => 'Dalabyggð', 'code' => 'DAB'],
            ['name' => 'Dalvíkurbyggð', 'code' => 'DAV'],
            ['name' => 'Eastern', 'code' => '7'],
            ['name' => 'Eyja- og Miklaholtshreppur', 'code' => 'EOM'],
            ['name' => 'Eyjafjarðarsveit', 'code' => 'EYF'],
            ['name' => 'Fjallabyggð', 'code' => 'FJL'],
            ['name' => 'Fjarðabyggð', 'code' => 'FJD'],
            ['name' => 'Fljótsdalshreppur', 'code' => 'FLR'],
            ['name' => 'Flóahreppur', 'code' => 'FLA'],
            ['name' => 'Garðabær', 'code' => 'GAR'],
            ['name' => 'Grímsnes- og Grafningshreppur', 'code' => 'GOG'],
            ['name' => 'Grindavík', 'code' => 'GRN'],
            ['name' => 'Grundarfjörður', 'code' => 'GRU'],
            ['name' => 'Grýtubakkahreppur', 'code' => 'GRY'],
            ['name' => 'Hafnarfjörður', 'code' => 'HAF'],
            ['name' => 'Hörðarsveit', 'code' => 'HRG'],
            ['name' => 'Hornafjörður', 'code' => 'SHF'],
            ['name' => 'Hrunamannahreppur', 'code' => 'HRU'],
            ['name' => 'Húnabyggð', 'code' => 'HUG'],
            ['name' => 'Húnaþing vestra', 'code' => 'HUV'],
            ['name' => 'Hvalfjarðarsveit', 'code' => 'HVA'],
            ['name' => 'Hveragerði', 'code' => 'HVE'],
            ['name' => 'Ísafjörður', 'code' => 'ISA'],
            ['name' => 'Kaldrananeshreppur', 'code' => 'KAL'],
            ['name' => 'Kjósarhreppur', 'code' => 'KJO'],
            ['name' => 'Kópavogur', 'code' => 'KOP'],
            ['name' => 'Langanesbyggð', 'code' => 'LAN'],
            ['name' => 'Mosfellsbær', 'code' => 'MOS'],
            ['name' => 'Múlaþing', 'code' => 'MUL'],
            ['name' => 'Mýrdalshreppur', 'code' => 'MYR'],
            ['name' => 'Norðurþing', 'code' => 'NOR'],
            ['name' => 'Northeastern', 'code' => '6'],
            ['name' => 'Northwestern', 'code' => '5'],
            ['name' => 'Ölfus', 'code' => 'SOL'],
            ['name' => 'Rangárþing eystra', 'code' => 'RGE'],
            ['name' => 'Rangárþing ytra', 'code' => 'RGY'],
            ['name' => 'Reykhólahreppur', 'code' => 'RHH'],
            ['name' => 'Reykjanesbær', 'code' => 'RKN'],
            ['name' => 'Reykjavík', 'code' => 'RKV'],
            ['name' => 'Seltjarnarnes', 'code' => 'SEL'],
            ['name' => 'Skaftárhreppur', 'code' => 'SKF'],
            ['name' => 'Skagabyggð', 'code' => 'SKG'],
            ['name' => 'Skagafjörður', 'code' => 'SKR'],
            ['name' => 'Skagaströnd', 'code' => 'SSS'],
            ['name' => 'Skeiða- og Gnúpverjahreppur', 'code' => 'SOG'],
            ['name' => 'Skorradalshreppur', 'code' => 'SKO'],
            ['name' => 'Snæfellsbær', 'code' => 'SNF'],
            ['name' => 'Southern', 'code' => '8'],
            ['name' => 'Southern Peninsula', 'code' => '2'],
            ['name' => 'Strandabyggð', 'code' => 'STR'],
            ['name' => 'Stykkishólmur', 'code' => 'STY'],
            ['name' => 'Súðavík', 'code' => 'SDV'],
            ['name' => 'Suðurnesjabær', 'code' => 'SDN'],
            ['name' => 'Svalbardsstrandarhreppur', 'code' => 'SBT'],
            ['name' => 'Tálknafjarðarhreppur', 'code' => 'TAL'],
            ['name' => 'Tjörneshreppur', 'code' => 'TJO'],
            ['name' => 'Vestmannaeyjar', 'code' => 'VEM'],
            ['name' => 'Vesturbyggð', 'code' => 'VER'],
            ['name' => 'Vogar', 'code' => 'SVG'],
            ['name' => 'Vopnafjarðarhreppur', 'code' => 'VOP'],
            ['name' => 'Western', 'code' => '3'],
            ['name' => 'Westfjords', 'code' => '4'],
            ['name' => 'Þingeyjarsveit', 'code' => 'THG'],
        ];
    }
}
