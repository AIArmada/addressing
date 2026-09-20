<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Russia;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class RussiaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_russia_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.russia';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'RU';
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
                        key: 'subject',
                        label: 'Federal Subject',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['oblast', 'republic', 'krai', 'okrug', 'federal_city', 'autonomous_oblast'],
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
                'oblast' => ['subject'],
                'republic' => ['subject'],
                'krai' => ['subject'],
                'okrug' => ['subject'],
                'federal_city' => ['subject'],
                'autonomous_oblast' => ['subject'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'RU', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/russia-address-areas.csv',
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
            'AD' => 'AD',
            'AL' => 'AL',
            'ALT' => 'ALT',
            'AMU' => 'AMU',
            'ARK' => 'ARK',
            'AST' => 'AST',
            'BA' => 'BA',
            'BEL' => 'BEL',
            'BRY' => 'BRY',
            'BU' => 'BU',
            'CE' => 'CE',
            'CHE' => 'CHE',
            'CHU' => 'CHU',
            'CU' => 'CU',
            'DA' => 'DA',
            'IN' => 'IN',
            'IRK' => 'IRK',
            'IVA' => 'IVA',
            'KAM' => 'KAM',
            'KB' => 'KB',
            'KC' => 'KC',
            'KDA' => 'KDA',
            'KEM' => 'KEM',
            'KGD' => 'KGD',
            'KGN' => 'KGN',
            'KHA' => 'KHA',
            'KHM' => 'KHM',
            'KIR' => 'KIR',
            'KK' => 'KK',
            'KL' => 'KL',
            'KLU' => 'KLU',
            'KO' => 'KO',
            'KOS' => 'KOS',
            'KR' => 'KR',
            'KRS' => 'KRS',
            'KYA' => 'KYA',
            'LEN' => 'LEN',
            'LIP' => 'LIP',
            'MAG' => 'MAG',
            'ME' => 'ME',
            'MO' => 'MO',
            'MOS' => 'MOS',
            'MOW' => 'MOW',
            'MUR' => 'MUR',
            'NEN' => 'NEN',
            'NGR' => 'NGR',
            'NIZ' => 'NIZ',
            'NVS' => 'NVS',
            'OMS' => 'OMS',
            'ORE' => 'ORE',
            'ORL' => 'ORL',
            'PER' => 'PER',
            'PNZ' => 'PNZ',
            'PRI' => 'PRI',
            'PSK' => 'PSK',
            'ROS' => 'ROS',
            'RYA' => 'RYA',
            'SA' => 'SA',
            'SAK' => 'SAK',
            'SAM' => 'SAM',
            'SAR' => 'SAR',
            'SE' => 'SE',
            'SMO' => 'SMO',
            'SPE' => 'SPE',
            'STA' => 'STA',
            'SVE' => 'SVE',
            'TA' => 'TA',
            'TAM' => 'TAM',
            'TOM' => 'TOM',
            'TUL' => 'TUL',
            'TVE' => 'TVE',
            'TY' => 'TY',
            'TYU' => 'TYU',
            'UD' => 'UD',
            'ULY' => 'ULY',
            'VGG' => 'VGG',
            'VLA' => 'VLA',
            'VLG' => 'VLG',
            'VOR' => 'VOR',
            'YAN' => 'YAN',
            'YAR' => 'YAR',
            'YEV' => 'YEV',
            'ZAB' => 'ZAB',
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
            ['name' => 'Adygea', 'code' => 'AD'],
            ['name' => 'Altai', 'code' => 'AL'],
            ['name' => 'Altai Krai', 'code' => 'ALT'],
            ['name' => 'Amur', 'code' => 'AMU'],
            ['name' => 'Arkhangelsk', 'code' => 'ARK'],
            ['name' => 'Astrakhan', 'code' => 'AST'],
            ['name' => 'Bashkortostan', 'code' => 'BA'],
            ['name' => 'Belgorod', 'code' => 'BEL'],
            ['name' => 'Bryansk', 'code' => 'BRY'],
            ['name' => 'Buryatia', 'code' => 'BU'],
            ['name' => 'Chechnya', 'code' => 'CE'],
            ['name' => 'Chelyabinsk', 'code' => 'CHE'],
            ['name' => 'Chukotka Okrug', 'code' => 'CHU'],
            ['name' => 'Chuvashia', 'code' => 'CU'],
            ['name' => 'Dagestan', 'code' => 'DA'],
            ['name' => 'Ingushetia', 'code' => 'IN'],
            ['name' => 'Irkutsk', 'code' => 'IRK'],
            ['name' => 'Ivanovo', 'code' => 'IVA'],
            ['name' => 'Kamchatka Krai', 'code' => 'KAM'],
            ['name' => 'Kabardino-Balkaria', 'code' => 'KB'],
            ['name' => 'Karachay-Cherkessia', 'code' => 'KC'],
            ['name' => 'Krasnodar Krai', 'code' => 'KDA'],
            ['name' => 'Kemerovo', 'code' => 'KEM'],
            ['name' => 'Kaliningrad', 'code' => 'KGD'],
            ['name' => 'Kurgan', 'code' => 'KGN'],
            ['name' => 'Khabarovsk Krai', 'code' => 'KHA'],
            ['name' => 'Khanty-Mansi Okrug', 'code' => 'KHM'],
            ['name' => 'Kirov', 'code' => 'KIR'],
            ['name' => 'Khakassia', 'code' => 'KK'],
            ['name' => 'Kalmykia', 'code' => 'KL'],
            ['name' => 'Kaluga', 'code' => 'KLU'],
            ['name' => 'Komi', 'code' => 'KO'],
            ['name' => 'Kostroma', 'code' => 'KOS'],
            ['name' => 'Karelia', 'code' => 'KR'],
            ['name' => 'Kursk', 'code' => 'KRS'],
            ['name' => 'Krasnoyarsk Krai', 'code' => 'KYA'],
            ['name' => 'Leningrad', 'code' => 'LEN'],
            ['name' => 'Lipetsk', 'code' => 'LIP'],
            ['name' => 'Magadan', 'code' => 'MAG'],
            ['name' => 'Mari El', 'code' => 'ME'],
            ['name' => 'Mordovia', 'code' => 'MO'],
            ['name' => 'Moscow Oblast', 'code' => 'MOS'],
            ['name' => 'Moscow', 'code' => 'MOW'],
            ['name' => 'Murmansk', 'code' => 'MUR'],
            ['name' => 'Nenets Okrug', 'code' => 'NEN'],
            ['name' => 'Novgorod', 'code' => 'NGR'],
            ['name' => 'Nizhny Novgorod', 'code' => 'NIZ'],
            ['name' => 'Novosibirsk', 'code' => 'NVS'],
            ['name' => 'Omsk', 'code' => 'OMS'],
            ['name' => 'Orenburg', 'code' => 'ORE'],
            ['name' => 'Oryol', 'code' => 'ORL'],
            ['name' => 'Perm Krai', 'code' => 'PER'],
            ['name' => 'Penza', 'code' => 'PNZ'],
            ['name' => 'Primorsky Krai', 'code' => 'PRI'],
            ['name' => 'Pskov', 'code' => 'PSK'],
            ['name' => 'Rostov', 'code' => 'ROS'],
            ['name' => 'Ryazan', 'code' => 'RYA'],
            ['name' => 'Sakha (Yakutia)', 'code' => 'SA'],
            ['name' => 'Sakhalin', 'code' => 'SAK'],
            ['name' => 'Samara', 'code' => 'SAM'],
            ['name' => 'Saratov', 'code' => 'SAR'],
            ['name' => 'North Ossetia-Alania', 'code' => 'SE'],
            ['name' => 'Smolensk', 'code' => 'SMO'],
            ['name' => 'Saint Petersburg', 'code' => 'SPE'],
            ['name' => 'Stavropol Krai', 'code' => 'STA'],
            ['name' => 'Sverdlovsk', 'code' => 'SVE'],
            ['name' => 'Tatarstan', 'code' => 'TA'],
            ['name' => 'Tambov', 'code' => 'TAM'],
            ['name' => 'Tomsk', 'code' => 'TOM'],
            ['name' => 'Tula', 'code' => 'TUL'],
            ['name' => 'Tver', 'code' => 'TVE'],
            ['name' => 'Tuva', 'code' => 'TY'],
            ['name' => 'Tyumen', 'code' => 'TYU'],
            ['name' => 'Udmurtia', 'code' => 'UD'],
            ['name' => 'Ulyanovsk', 'code' => 'ULY'],
            ['name' => 'Volgograd', 'code' => 'VGG'],
            ['name' => 'Vladimir', 'code' => 'VLA'],
            ['name' => 'Vologda', 'code' => 'VLG'],
            ['name' => 'Voronezh', 'code' => 'VOR'],
            ['name' => 'Yamalo-Nenets Okrug', 'code' => 'YAN'],
            ['name' => 'Yaroslavl', 'code' => 'YAR'],
            ['name' => 'Jewish Autonomous Oblast', 'code' => 'YEV'],
            ['name' => 'Zabaykalsky Krai', 'code' => 'ZAB'],
        ];
    }
}
