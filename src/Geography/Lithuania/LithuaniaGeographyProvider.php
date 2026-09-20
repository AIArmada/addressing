<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Lithuania;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class LithuaniaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_lithuania_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.lithuania';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'LT';
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
                        key: 'county',
                        label: 'County / District Municipality / Municipality / City Municipality',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['county', 'district_municipality', 'municipality', 'city_municipality'],
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
                'county' => ['county'],
                'district_municipality' => ['district_municipality'],
                'municipality' => ['municipality'],
                'city_municipality' => ['city_municipality'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'LT', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/lithuania-address-areas.csv',
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
            '01' => '01',
            '03' => '03',
            '02' => '02',
            'AL' => 'AL',
            '04' => '04',
            '05' => '05',
            '06' => '06',
            '07' => '07',
            '08' => '08',
            '09' => '09',
            '10' => '10',
            '11' => '11',
            '12' => '12',
            '13' => '13',
            '14' => '14',
            'KU' => 'KU',
            '16' => '16',
            '15' => '15',
            '17' => '17',
            '18' => '18',
            '19' => '19',
            'KL' => 'KL',
            '21' => '21',
            '20' => '20',
            '22' => '22',
            '23' => '23',
            '24' => '24',
            '25' => '25',
            'MR' => 'MR',
            '26' => '26',
            '27' => '27',
            '28' => '28',
            '29' => '29',
            '30' => '30',
            '31' => '31',
            '32' => '32',
            '33' => '33',
            'PN' => 'PN',
            '34' => '34',
            '35' => '35',
            '36' => '36',
            '37' => '37',
            '38' => '38',
            '39' => '39',
            '40' => '40',
            '41' => '41',
            '42' => '42',
            'SA' => 'SA',
            '43' => '43',
            '44' => '44',
            '45' => '45',
            '46' => '46',
            '47' => '47',
            '48' => '48',
            '49' => '49',
            'TA' => 'TA',
            '50' => '50',
            '51' => '51',
            'TE' => 'TE',
            '52' => '52',
            '53' => '53',
            '54' => '54',
            'UT' => 'UT',
            '55' => '55',
            '56' => '56',
            '58' => '58',
            '57' => '57',
            'VL' => 'VL',
            '59' => '59',
            '60' => '60',
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
            ['name' => 'Akmenė', 'code' => '01'],
            ['name' => 'Alytus', 'code' => '03'],
            ['name' => 'Alytus', 'code' => '02'],
            ['name' => 'Alytus', 'code' => 'AL'],
            ['name' => 'Anykščiai', 'code' => '04'],
            ['name' => 'Birštonas', 'code' => '05'],
            ['name' => 'Biržai', 'code' => '06'],
            ['name' => 'Druskininkai', 'code' => '07'],
            ['name' => 'Elektrėnai', 'code' => '08'],
            ['name' => 'Ignalina', 'code' => '09'],
            ['name' => 'Jonava', 'code' => '10'],
            ['name' => 'Joniškis', 'code' => '11'],
            ['name' => 'Jurbarkas', 'code' => '12'],
            ['name' => 'Kaišiadorys', 'code' => '13'],
            ['name' => 'Kalvarija', 'code' => '14'],
            ['name' => 'Kaunas', 'code' => 'KU'],
            ['name' => 'Kaunas', 'code' => '16'],
            ['name' => 'Kaunas', 'code' => '15'],
            ['name' => 'Kazlų Rūda', 'code' => '17'],
            ['name' => 'Kėdainiai', 'code' => '18'],
            ['name' => 'Kelmė', 'code' => '19'],
            ['name' => 'Klaipėda', 'code' => 'KL'],
            ['name' => 'Klaipėda', 'code' => '21'],
            ['name' => 'Klaipėdos miestas', 'code' => '20'],
            ['name' => 'Kretinga', 'code' => '22'],
            ['name' => 'Kupiškis', 'code' => '23'],
            ['name' => 'Lazdijai', 'code' => '24'],
            ['name' => 'Marijampolė', 'code' => '25'],
            ['name' => 'Marijampolė', 'code' => 'MR'],
            ['name' => 'Mažeikiai', 'code' => '26'],
            ['name' => 'Molėtai', 'code' => '27'],
            ['name' => 'Neringa', 'code' => '28'],
            ['name' => 'Pagėgiai', 'code' => '29'],
            ['name' => 'Pakruojis', 'code' => '30'],
            ['name' => 'Palanga', 'code' => '31'],
            ['name' => 'Panevėžio miestas', 'code' => '32'],
            ['name' => 'Panevėžys', 'code' => '33'],
            ['name' => 'Panevėžys', 'code' => 'PN'],
            ['name' => 'Pasvalys', 'code' => '34'],
            ['name' => 'Plungė', 'code' => '35'],
            ['name' => 'Prienai', 'code' => '36'],
            ['name' => 'Radviliškis', 'code' => '37'],
            ['name' => 'Raseiniai', 'code' => '38'],
            ['name' => 'Rietavas', 'code' => '39'],
            ['name' => 'Rokiškis', 'code' => '40'],
            ['name' => 'Šakiai', 'code' => '41'],
            ['name' => 'Šalčininkai', 'code' => '42'],
            ['name' => 'Šiauliai', 'code' => 'SA'],
            ['name' => 'Šiauliai', 'code' => '43'],
            ['name' => 'Šiauliai', 'code' => '44'],
            ['name' => 'Šilalė', 'code' => '45'],
            ['name' => 'Šilutė', 'code' => '46'],
            ['name' => 'Širvintos', 'code' => '47'],
            ['name' => 'Skuodas', 'code' => '48'],
            ['name' => 'Švenčionys', 'code' => '49'],
            ['name' => 'Tauragė', 'code' => 'TA'],
            ['name' => 'Tauragė', 'code' => '50'],
            ['name' => 'Telšiai', 'code' => '51'],
            ['name' => 'Telšiai', 'code' => 'TE'],
            ['name' => 'Trakai', 'code' => '52'],
            ['name' => 'Ukmergė', 'code' => '53'],
            ['name' => 'Utena', 'code' => '54'],
            ['name' => 'Utena', 'code' => 'UT'],
            ['name' => 'Varėna', 'code' => '55'],
            ['name' => 'Vilkaviškis', 'code' => '56'],
            ['name' => 'Vilnius', 'code' => '58'],
            ['name' => 'Vilnius', 'code' => '57'],
            ['name' => 'Vilnius', 'code' => 'VL'],
            ['name' => 'Visaginas', 'code' => '59'],
            ['name' => 'Zarasai', 'code' => '60'],
        ];
    }
}
