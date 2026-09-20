<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Thailand;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class ThailandGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_thailand_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.thailand';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'TH';
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
                        key: 'province',
                        label: 'Province / Metropolitan Administration',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['province', 'metropolitan_administration'],
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
                'province' => ['province'],
                'metropolitan_administration' => ['province'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'TH', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/thailand-address-areas.csv',
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
            '10' => '10',
            '11' => '11',
            '12' => '12',
            '13' => '13',
            '14' => '14',
            '15' => '15',
            '16' => '16',
            '17' => '17',
            '18' => '18',
            '19' => '19',
            '20' => '20',
            '21' => '21',
            '22' => '22',
            '23' => '23',
            '24' => '24',
            '25' => '25',
            '26' => '26',
            '27' => '27',
            '30' => '30',
            '31' => '31',
            '32' => '32',
            '33' => '33',
            '34' => '34',
            '35' => '35',
            '36' => '36',
            '37' => '37',
            '38' => '38',
            '39' => '39',
            '40' => '40',
            '41' => '41',
            '42' => '42',
            '43' => '43',
            '44' => '44',
            '45' => '45',
            '46' => '46',
            '47' => '47',
            '48' => '48',
            '49' => '49',
            '50' => '50',
            '51' => '51',
            '52' => '52',
            '53' => '53',
            '54' => '54',
            '55' => '55',
            '56' => '56',
            '57' => '57',
            '58' => '58',
            '60' => '60',
            '61' => '61',
            '62' => '62',
            '63' => '63',
            '64' => '64',
            '65' => '65',
            '66' => '66',
            '67' => '67',
            '70' => '70',
            '71' => '71',
            '72' => '72',
            '73' => '73',
            '74' => '74',
            '75' => '75',
            '76' => '76',
            '77' => '77',
            '80' => '80',
            '81' => '81',
            '82' => '82',
            '83' => '83',
            '84' => '84',
            '85' => '85',
            '86' => '86',
            '90' => '90',
            '91' => '91',
            '92' => '92',
            '93' => '93',
            '94' => '94',
            '95' => '95',
            '96' => '96',
            'S' => 'S',
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
            ['name' => 'Bangkok', 'code' => '10'],
            ['name' => 'Samut Prakan', 'code' => '11'],
            ['name' => 'Nonthaburi', 'code' => '12'],
            ['name' => 'Pathum Thani', 'code' => '13'],
            ['name' => 'Phra Nakhon Si Ayutthaya', 'code' => '14'],
            ['name' => 'Ang Thong', 'code' => '15'],
            ['name' => 'Lop Buri', 'code' => '16'],
            ['name' => 'Sing Buri', 'code' => '17'],
            ['name' => 'Chai Nat', 'code' => '18'],
            ['name' => 'Saraburi', 'code' => '19'],
            ['name' => 'Chon Buri', 'code' => '20'],
            ['name' => 'Rayong', 'code' => '21'],
            ['name' => 'Chanthaburi', 'code' => '22'],
            ['name' => 'Trat', 'code' => '23'],
            ['name' => 'Chachoengsao', 'code' => '24'],
            ['name' => 'Prachin Buri', 'code' => '25'],
            ['name' => 'Nakhon Nayok', 'code' => '26'],
            ['name' => 'Sa Kaeo', 'code' => '27'],
            ['name' => 'Nakhon Ratchasima', 'code' => '30'],
            ['name' => 'Buri Ram', 'code' => '31'],
            ['name' => 'Surin', 'code' => '32'],
            ['name' => 'Si Sa Ket', 'code' => '33'],
            ['name' => 'Ubon Ratchathani', 'code' => '34'],
            ['name' => 'Yasothon', 'code' => '35'],
            ['name' => 'Chaiyaphum', 'code' => '36'],
            ['name' => 'Amnat Charoen', 'code' => '37'],
            ['name' => 'Bueng Kan', 'code' => '38'],
            ['name' => 'Nong Bua Lam Phu', 'code' => '39'],
            ['name' => 'Khon Kaen', 'code' => '40'],
            ['name' => 'Udon Thani', 'code' => '41'],
            ['name' => 'Loei', 'code' => '42'],
            ['name' => 'Nong Khai', 'code' => '43'],
            ['name' => 'Maha Sarakham', 'code' => '44'],
            ['name' => 'Roi Et', 'code' => '45'],
            ['name' => 'Kalasin', 'code' => '46'],
            ['name' => 'Sakon Nakhon', 'code' => '47'],
            ['name' => 'Nakhon Phanom', 'code' => '48'],
            ['name' => 'Mukdahan', 'code' => '49'],
            ['name' => 'Chiang Mai', 'code' => '50'],
            ['name' => 'Lamphun', 'code' => '51'],
            ['name' => 'Lampang', 'code' => '52'],
            ['name' => 'Uttaradit', 'code' => '53'],
            ['name' => 'Phrae', 'code' => '54'],
            ['name' => 'Nan', 'code' => '55'],
            ['name' => 'Phayao', 'code' => '56'],
            ['name' => 'Chiang Rai', 'code' => '57'],
            ['name' => 'Mae Hong Son', 'code' => '58'],
            ['name' => 'Nakhon Sawan', 'code' => '60'],
            ['name' => 'Uthai Thani', 'code' => '61'],
            ['name' => 'Kamphaeng Phet', 'code' => '62'],
            ['name' => 'Tak', 'code' => '63'],
            ['name' => 'Sukhothai', 'code' => '64'],
            ['name' => 'Phitsanulok', 'code' => '65'],
            ['name' => 'Phichit', 'code' => '66'],
            ['name' => 'Phetchabun', 'code' => '67'],
            ['name' => 'Ratchaburi', 'code' => '70'],
            ['name' => 'Kanchanaburi', 'code' => '71'],
            ['name' => 'Suphan Buri', 'code' => '72'],
            ['name' => 'Nakhon Pathom', 'code' => '73'],
            ['name' => 'Samut Sakhon', 'code' => '74'],
            ['name' => 'Samut Songkhram', 'code' => '75'],
            ['name' => 'Phetchaburi', 'code' => '76'],
            ['name' => 'Prachuap Khiri Khan', 'code' => '77'],
            ['name' => 'Nakhon Si Thammarat', 'code' => '80'],
            ['name' => 'Krabi', 'code' => '81'],
            ['name' => 'Phangnga', 'code' => '82'],
            ['name' => 'Phuket', 'code' => '83'],
            ['name' => 'Surat Thani', 'code' => '84'],
            ['name' => 'Ranong', 'code' => '85'],
            ['name' => 'Chumphon', 'code' => '86'],
            ['name' => 'Songkhla', 'code' => '90'],
            ['name' => 'Satun', 'code' => '91'],
            ['name' => 'Trang', 'code' => '92'],
            ['name' => 'Phatthalung', 'code' => '93'],
            ['name' => 'Pattani', 'code' => '94'],
            ['name' => 'Yala', 'code' => '95'],
            ['name' => 'Narathiwat', 'code' => '96'],
            ['name' => 'Pattaya', 'code' => 'S'],
        ];
    }
}
