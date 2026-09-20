<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Turkiye;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class TurkiyeGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_turkiye_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.turkiye';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'TR';
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
                        label: 'Province',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['province'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'district',
                        label: 'District',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['district'],
                        areaLevels: [2],
                        parentKey: 'province',
                        assignmentRole: 'district',
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
                'district' => ['district'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'TR', 'is_primary' => true],
                $areaRoles,
            );
        }

        return $roles;
    }

    /** @return array<string, list<array{name: string, name_type?: string, is_preferred?: bool}>> */
    public function areaNames(AddressCountry $country): array
    {
        return [
            'tr:district:ankara:kahramankazan' => [
                ['name' => 'Kazan', 'name_type' => 'historic'],
            ],
            'tr:district:zonguldak:eregli' => [
                ['name' => 'Karadeniz Ereğli', 'name_type' => 'common'],
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
            __DIR__ . '/../../../resources/geography/turkiye-address-areas.csv',
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
            '01' => '01',
            '02' => '02',
            '03' => '03',
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
            '28' => '28',
            '29' => '29',
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
            '59' => '59',
            '60' => '60',
            '61' => '61',
            '62' => '62',
            '63' => '63',
            '64' => '64',
            '65' => '65',
            '66' => '66',
            '67' => '67',
            '68' => '68',
            '69' => '69',
            '70' => '70',
            '71' => '71',
            '72' => '72',
            '73' => '73',
            '74' => '74',
            '75' => '75',
            '76' => '76',
            '77' => '77',
            '78' => '78',
            '79' => '79',
            '80' => '80',
            '81' => '81',
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
            ['name' => 'Adana', 'code' => '01'],
            ['name' => 'Adıyaman', 'code' => '02'],
            ['name' => 'Afyonkarahisar', 'code' => '03'],
            ['name' => 'Ağrı', 'code' => '04'],
            ['name' => 'Amasya', 'code' => '05'],
            ['name' => 'Ankara', 'code' => '06'],
            ['name' => 'Antalya', 'code' => '07'],
            ['name' => 'Artvin', 'code' => '08'],
            ['name' => 'Aydın', 'code' => '09'],
            ['name' => 'Balıkesir', 'code' => '10'],
            ['name' => 'Bilecik', 'code' => '11'],
            ['name' => 'Bingöl', 'code' => '12'],
            ['name' => 'Bitlis', 'code' => '13'],
            ['name' => 'Bolu', 'code' => '14'],
            ['name' => 'Burdur', 'code' => '15'],
            ['name' => 'Bursa', 'code' => '16'],
            ['name' => 'Çanakkale', 'code' => '17'],
            ['name' => 'Çankırı', 'code' => '18'],
            ['name' => 'Çorum', 'code' => '19'],
            ['name' => 'Denizli', 'code' => '20'],
            ['name' => 'Diyarbakır', 'code' => '21'],
            ['name' => 'Edirne', 'code' => '22'],
            ['name' => 'Elazığ', 'code' => '23'],
            ['name' => 'Erzincan', 'code' => '24'],
            ['name' => 'Erzurum', 'code' => '25'],
            ['name' => 'Eskişehir', 'code' => '26'],
            ['name' => 'Gaziantep', 'code' => '27'],
            ['name' => 'Giresun', 'code' => '28'],
            ['name' => 'Gümüşhane', 'code' => '29'],
            ['name' => 'Hakkâri', 'code' => '30'],
            ['name' => 'Hatay', 'code' => '31'],
            ['name' => 'Isparta', 'code' => '32'],
            ['name' => 'Mersin', 'code' => '33'],
            ['name' => 'İstanbul', 'code' => '34'],
            ['name' => 'İzmir', 'code' => '35'],
            ['name' => 'Kars', 'code' => '36'],
            ['name' => 'Kastamonu', 'code' => '37'],
            ['name' => 'Kayseri', 'code' => '38'],
            ['name' => 'Kırklareli', 'code' => '39'],
            ['name' => 'Kırşehir', 'code' => '40'],
            ['name' => 'Kocaeli', 'code' => '41'],
            ['name' => 'Konya', 'code' => '42'],
            ['name' => 'Kütahya', 'code' => '43'],
            ['name' => 'Malatya', 'code' => '44'],
            ['name' => 'Manisa', 'code' => '45'],
            ['name' => 'Kahramanmaraş', 'code' => '46'],
            ['name' => 'Mardin', 'code' => '47'],
            ['name' => 'Muğla', 'code' => '48'],
            ['name' => 'Muş', 'code' => '49'],
            ['name' => 'Nevşehir', 'code' => '50'],
            ['name' => 'Niğde', 'code' => '51'],
            ['name' => 'Ordu', 'code' => '52'],
            ['name' => 'Rize', 'code' => '53'],
            ['name' => 'Sakarya', 'code' => '54'],
            ['name' => 'Samsun', 'code' => '55'],
            ['name' => 'Siirt', 'code' => '56'],
            ['name' => 'Sinop', 'code' => '57'],
            ['name' => 'Sivas', 'code' => '58'],
            ['name' => 'Tekirdağ', 'code' => '59'],
            ['name' => 'Tokat', 'code' => '60'],
            ['name' => 'Trabzon', 'code' => '61'],
            ['name' => 'Tunceli', 'code' => '62'],
            ['name' => 'Şanlıurfa', 'code' => '63'],
            ['name' => 'Uşak', 'code' => '64'],
            ['name' => 'Van', 'code' => '65'],
            ['name' => 'Yozgat', 'code' => '66'],
            ['name' => 'Zonguldak', 'code' => '67'],
            ['name' => 'Aksaray', 'code' => '68'],
            ['name' => 'Bayburt', 'code' => '69'],
            ['name' => 'Karaman', 'code' => '70'],
            ['name' => 'Kırıkkale', 'code' => '71'],
            ['name' => 'Batman', 'code' => '72'],
            ['name' => 'Şırnak', 'code' => '73'],
            ['name' => 'Bartın', 'code' => '74'],
            ['name' => 'Ardahan', 'code' => '75'],
            ['name' => 'Iğdır', 'code' => '76'],
            ['name' => 'Yalova', 'code' => '77'],
            ['name' => 'Karabük', 'code' => '78'],
            ['name' => 'Kilis', 'code' => '79'],
            ['name' => 'Osmaniye', 'code' => '80'],
            ['name' => 'Düzce', 'code' => '81'],
        ];
    }
}
