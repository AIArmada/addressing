<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Qatar;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class QatarGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_qatar_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.qatar';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'QA';
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
                        label: 'Municipality',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['municipality'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'zone',
                        label: 'Zone',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['zone'],
                        areaLevels: [2],
                        parentKey: 'municipality',
                        assignmentRole: 'zone',
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
                'zone' => ['zone'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'QA', 'is_primary' => true],
                $areaRoles,
            );
        }

        return $roles;
    }

    /** @return array<string, list<array{name: string, name_type?: string, is_preferred?: bool}>> */
    public function areaNames(AddressCountry $country): array
    {
        return [
            'qa:municipality:doha' => [
                ['name' => 'Ad Dawhah', 'name_type' => 'alternative'],
            ],
            'qa:municipality:al-shamal' => [
                ['name' => 'Madinat ash Shamal', 'name_type' => 'alternative'],
            ],
            'qa:zone:doha:1' => [
                ['name' => 'Al Jasra', 'name_type' => 'official'],
            ],
            'qa:zone:doha:2' => [
                ['name' => 'Al Bidda', 'name_type' => 'official'],
            ],
            'qa:zone:doha:3' => [
                ['name' => 'Fereej Mohammed Bin Jasim / Mushaireb', 'name_type' => 'official'],
            ],
            'qa:zone:doha:4' => [
                ['name' => 'Mushaireb', 'name_type' => 'official'],
            ],
            'qa:zone:doha:5' => [
                ['name' => 'Al Najada / Brahat Al Jufairy / Fereej Al Asmakh', 'name_type' => 'official'],
            ],
            'qa:zone:doha:6' => [
                ['name' => 'Old Al Ghanim', 'name_type' => 'official'],
            ],
            'qa:zone:doha:7' => [
                ['name' => 'Al Souq', 'name_type' => 'official'],
            ],
            'qa:zone:doha:12' => [
                ['name' => 'Al Bidda', 'name_type' => 'official'],
            ],
            'qa:zone:doha:13' => [
                ['name' => 'Mushaireb', 'name_type' => 'official'],
            ],
            'qa:zone:doha:14' => [
                ['name' => 'Fereej Abdel Aziz', 'name_type' => 'official'],
            ],
            'qa:zone:doha:15' => [
                ['name' => 'Al Doha Al Jadeeda', 'name_type' => 'official'],
            ],
            'qa:zone:doha:16' => [
                ['name' => 'Old Al Ghanim', 'name_type' => 'official'],
            ],
            'qa:zone:doha:17' => [
                ['name' => 'Al Rufaa / Old Al Hitmi', 'name_type' => 'official'],
            ],
            'qa:zone:doha:18' => [
                ['name' => 'Slata / Al Mirqab', 'name_type' => 'official'],
            ],
            'qa:zone:doha:19' => [
                ['name' => 'Doha Port', 'name_type' => 'official'],
            ],
            'qa:zone:doha:20' => [
                ['name' => 'Wadi Al Sail', 'name_type' => 'official'],
            ],
            'qa:zone:doha:21' => [
                ['name' => 'Rumaila', 'name_type' => 'official'],
            ],
            'qa:zone:doha:22' => [
                ['name' => 'Fereej Bin Mahmoud', 'name_type' => 'official'],
            ],
            'qa:zone:doha:23' => [
                ['name' => 'Fereej Bin Mahmoud', 'name_type' => 'official'],
            ],
            'qa:zone:doha:24' => [
                ['name' => 'Rawdat Al Khail', 'name_type' => 'official'],
            ],
            'qa:zone:doha:25' => [
                ['name' => 'Al Mansoura / Fereej Bin Dirham', 'name_type' => 'official'],
            ],
            'qa:zone:doha:26' => [
                ['name' => 'Najma', 'name_type' => 'official'],
            ],
            'qa:zone:doha:27' => [
                ['name' => 'Umm Ghuwailina', 'name_type' => 'official'],
            ],
            'qa:zone:doha:28' => [
                ['name' => 'Al Khulaifat / Ras Bu Abboud', 'name_type' => 'official'],
            ],
            'qa:zone:doha:29' => [
                ['name' => 'Ras Bu Abboud', 'name_type' => 'official'],
            ],
            'qa:zone:doha:30' => [
                ['name' => 'Duhail', 'name_type' => 'official'],
            ],
            'qa:zone:doha:31' => [
                ['name' => 'Umm Lekhba', 'name_type' => 'official'],
            ],
            'qa:zone:doha:32' => [
                ['name' => 'Madinat Khalifa North / Dahl Al Hamam', 'name_type' => 'official'],
            ],
            'qa:zone:doha:33' => [
                ['name' => 'Al Markhiya', 'name_type' => 'official'],
            ],
            'qa:zone:doha:34' => [
                ['name' => 'Madinat Khalifa South', 'name_type' => 'official'],
            ],
            'qa:zone:doha:35' => [
                ['name' => 'Fereej Kulaib', 'name_type' => 'official'],
            ],
            'qa:zone:doha:36' => [
                ['name' => 'Al Messila', 'name_type' => 'official'],
            ],
            'qa:zone:doha:37' => [
                ['name' => 'Fereej Bin Omran / New Al Hitmi / Hamad Medical City', 'name_type' => 'official'],
            ],
            'qa:zone:doha:38' => [
                ['name' => 'Al Sadd', 'name_type' => 'official'],
            ],
            'qa:zone:doha:39' => [
                ['name' => 'Al Sadd / New Al Mirqab / Fereej Al Nasr', 'name_type' => 'official'],
            ],
            'qa:zone:doha:40' => [
                ['name' => 'New Slata', 'name_type' => 'official'],
            ],
            'qa:zone:doha:41' => [
                ['name' => 'Nuaija', 'name_type' => 'official'],
            ],
            'qa:zone:doha:42' => [
                ['name' => 'Al Hilal', 'name_type' => 'official'],
            ],
            'qa:zone:doha:43' => [
                ['name' => 'Nuaija', 'name_type' => 'official'],
            ],
            'qa:zone:doha:44' => [
                ['name' => 'Nuaija', 'name_type' => 'official'],
            ],
            'qa:zone:doha:45' => [
                ['name' => 'Old Airport', 'name_type' => 'official'],
            ],
            'qa:zone:doha:46' => [
                ['name' => 'Al Thumama', 'name_type' => 'official'],
            ],
            'qa:zone:doha:47' => [
                ['name' => 'Al Thumama', 'name_type' => 'official'],
            ],
            'qa:zone:doha:48' => [
                ['name' => 'Doha International Airport', 'name_type' => 'official'],
            ],
            'qa:zone:doha:49' => [
                ['name' => 'Doha International Airport', 'name_type' => 'official'],
            ],
            'qa:zone:doha:50' => [
                ['name' => 'Al Thumama', 'name_type' => 'official'],
            ],
            'qa:zone:doha:57' => [
                ['name' => 'Industrial Area', 'name_type' => 'official'],
            ],
            'qa:zone:doha:58' => [
                ['name' => 'Wholesale Market', 'name_type' => 'official'],
            ],
            'qa:zone:doha:60' => [
                ['name' => 'Al Dafna', 'name_type' => 'official'],
            ],
            'qa:zone:doha:61' => [
                ['name' => 'Al Dafna / Al Qassar', 'name_type' => 'official'],
            ],
            'qa:zone:doha:62' => [
                ['name' => 'Lekhwair', 'name_type' => 'official'],
            ],
            'qa:zone:doha:63' => [
                ['name' => 'Onaiza', 'name_type' => 'official'],
            ],
            'qa:zone:doha:64' => [
                ['name' => 'Lejbailat', 'name_type' => 'official'],
            ],
            'qa:zone:doha:65' => [
                ['name' => 'Onaiza', 'name_type' => 'official'],
            ],
            'qa:zone:doha:66' => [
                ['name' => 'Onaiza / Leqtaifiya / Al Qassar', 'name_type' => 'official'],
            ],
            'qa:zone:doha:67' => [
                ['name' => 'Hazm Al Markhiya', 'name_type' => 'official'],
            ],
            'qa:zone:doha:68' => [
                ['name' => 'Jelaiah / Al Tarfa / Jeryan Nejaima', 'name_type' => 'official'],
            ],
            'qa:zone:al-khor:74' => [
                ['name' => 'Simaisma / Al Jeryan / Al Khor', 'name_type' => 'official'],
            ],
            'qa:zone:al-khor:75' => [
                ['name' => 'Al Thakhira/Rass Laffan/Umm Birka', 'name_type' => 'official'],
            ],
            'qa:zone:al-khor:76' => [
                ['name' => 'Al Ghuwairiya', 'name_type' => 'official'],
            ],
            'qa:zone:al-shamal:77' => [
                ['name' => 'Fuwairit/Ain Sinan/Madinat Al Kaaban', 'name_type' => 'official'],
            ],
            'qa:zone:al-shamal:78' => [
                ['name' => 'Abu Dhalouf/Al Zubara', 'name_type' => 'official'],
            ],
            'qa:zone:al-shamal:79' => [
                ['name' => 'Al Ruwais/Al Shamal', 'name_type' => 'official'],
            ],
            'qa:zone:al-rayyan:51' => [
                ['name' => 'Al Gharrafa / Gharrafat Al Rayyan / Izghawa / Bani Hajer / Al Seej / Rawdat Egdaim / Al Themaid', 'name_type' => 'official'],
            ],
            'qa:zone:al-rayyan:52' => [
                ['name' => 'Al Luqta / Lebday / Old Al Rayyan / Al Shagub / Fereej Al Zaeem', 'name_type' => 'official'],
            ],
            'qa:zone:al-rayyan:53' => [
                ['name' => 'New Al Rayyan / Al Wajba / Muaither', 'name_type' => 'official'],
            ],
            'qa:zone:al-rayyan:54' => [
                ['name' => 'Fereej Al Amir / Luaib / Muraikh / Baaya / Mehairja / Fereej Al Soudan', 'name_type' => 'official'],
            ],
            'qa:zone:al-rayyan:55' => [
                ['name' => 'Fereej Al Soudan / Al Waab / Al Aziziya / New Fereej Al Ghanim / Fereej Al Murra / Fereej Al Manaseer / Bu Sidra / Muaither / Al Sailiya / Al Mearad', 'name_type' => 'official'],
            ],
            'qa:zone:al-rayyan:56' => [
                ['name' => 'Fereej Al Asiri / New Fereej Al Khulaifat / Bu Samra / Al Maamoura / Bu Hamour / Mesaimeer / Ain Khaled', 'name_type' => 'official'],
            ],
            'qa:zone:al-rayyan:81' => [
                ['name' => 'Mebaireek', 'name_type' => 'official'],
            ],
            'qa:zone:al-rayyan:83' => [
                ['name' => 'Al Karaana', 'name_type' => 'official'],
            ],
            'qa:zone:al-rayyan:96' => [
                ['name' => 'Abu Samra', 'name_type' => 'official'],
            ],
            'qa:zone:al-rayyan:97' => [
                ['name' => 'Sawda Natheel', 'name_type' => 'official'],
            ],
            'qa:zone:al-sheehaniya:72' => [
                ['name' => 'Al Utouriya', 'name_type' => 'official'],
            ],
            'qa:zone:al-sheehaniya:73' => [
                ['name' => 'Lijmiliya', 'name_type' => 'official'],
            ],
            'qa:zone:al-sheehaniya:80' => [
                ['name' => 'Al Sheehaniya', 'name_type' => 'official'],
            ],
            'qa:zone:al-sheehaniya:82' => [
                ['name' => 'Rawdat Rashed', 'name_type' => 'official'],
            ],
            'qa:zone:al-sheehaniya:84' => [
                ['name' => 'Umm Bab', 'name_type' => 'official'],
            ],
            'qa:zone:al-sheehaniya:85' => [
                ['name' => 'Al Nasraniya', 'name_type' => 'official'],
            ],
            'qa:zone:al-sheehaniya:86' => [
                ['name' => 'Dukhan', 'name_type' => 'official'],
            ],
            'qa:zone:umm-salal:71' => [
                ['name' => 'Al Kharaitiyat / Izghawa / Umm Slal Mohammed / Bu Fesseela / Umm Slal Ali / Umm Al Amad / Umm Obairiya / Lekhshaina / Sunay Lehmaidi', 'name_type' => 'official'],
            ],
            'qa:zone:al-wakrah:90' => [
                ['name' => 'Al Wakra', 'name_type' => 'official'],
            ],
            'qa:zone:al-wakrah:91' => [
                ['name' => 'Al Thumama / Al Wukair/Al Mashaf', 'name_type' => 'official'],
            ],
            'qa:zone:al-wakrah:92' => [
                ['name' => 'Mesaieed', 'name_type' => 'official'],
            ],
            'qa:zone:al-wakrah:93' => [
                ['name' => 'Mesaieed Industrial Area', 'name_type' => 'official'],
            ],
            'qa:zone:al-wakrah:94' => [
                ['name' => 'Shagra', 'name_type' => 'official'],
            ],
            'qa:zone:al-wakrah:95' => [
                ['name' => 'Al Kharrara', 'name_type' => 'official'],
            ],
            'qa:zone:al-wakrah:98' => [
                ['name' => 'Al Adaid', 'name_type' => 'official'],
            ],
            'qa:zone:al-daayen:69' => [
                ['name' => 'Jabal Thuaileb / Al Kharayej / Lusail / Al Egla / Wadi Al Banat', 'name_type' => 'official'],
            ],
            'qa:zone:al-daayen:70' => [
                ['name' => 'Leabaib / Al Ebb / Jeryan Jenaihat / Al Kheesa / Rawdat Al Hamama / Wadi Al Wasaah / Al Sakhama / Al Masrouhiya / Wadi Lusail / Lusail / Umm Garn / Al Daayen', 'name_type' => 'official'],
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
            __DIR__ . '/../../../resources/geography/qatar-address-areas.csv',
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
            'DA' => 'DA',
            'KH' => 'KH',
            'MS' => 'MS',
            'RA' => 'RA',
            'SH' => 'SH',
            'US' => 'US',
            'WA' => 'WA',
            'ZA' => 'ZA',
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
            ['name' => 'Doha', 'code' => 'DA'],
            ['name' => 'Al Khor', 'code' => 'KH'],
            ['name' => 'Al Shamal', 'code' => 'MS'],
            ['name' => 'Al Rayyan', 'code' => 'RA'],
            ['name' => 'Al Sheehaniya', 'code' => 'SH'],
            ['name' => 'Umm Salal', 'code' => 'US'],
            ['name' => 'Al Wakrah', 'code' => 'WA'],
            ['name' => 'Al Daayen', 'code' => 'ZA'],
        ];
    }
}
