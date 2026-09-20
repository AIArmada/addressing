<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Slovenia;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class SloveniaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_slovenia_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.slovenia';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'SI';
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
                        label: 'Municipality / Urban Municipality',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['municipality', 'urban_municipality'],
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
                'urban_municipality' => ['urban_municipality'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'SI', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/slovenia-address-areas.csv',
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
            '001' => '001',
            '213' => '213',
            '195' => '195',
            '002' => '002',
            '148' => '148',
            '149' => '149',
            '003' => '003',
            '150' => '150',
            '004' => '004',
            '005' => '005',
            '006' => '006',
            '151' => '151',
            '007' => '007',
            '009' => '009',
            '008' => '008',
            '152' => '152',
            '011' => '011',
            '012' => '012',
            '013' => '013',
            '014' => '014',
            '153' => '153',
            '196' => '196',
            '015' => '015',
            '016' => '016',
            '017' => '017',
            '018' => '018',
            '019' => '019',
            '154' => '154',
            '020' => '020',
            '155' => '155',
            '021' => '021',
            '156' => '156',
            '022' => '022',
            '157' => '157',
            '023' => '023',
            '024' => '024',
            '025' => '025',
            '026' => '026',
            '027' => '027',
            '028' => '028',
            '207' => '207',
            '029' => '029',
            '030' => '030',
            '031' => '031',
            '158' => '158',
            '032' => '032',
            '159' => '159',
            '160' => '160',
            '161' => '161',
            '162' => '162',
            '034' => '034',
            '035' => '035',
            '036' => '036',
            '037' => '037',
            '038' => '038',
            '039' => '039',
            '040' => '040',
            '041' => '041',
            '163' => '163',
            '042' => '042',
            '043' => '043',
            '044' => '044',
            '045' => '045',
            '046' => '046',
            '047' => '047',
            '048' => '048',
            '049' => '049',
            '164' => '164',
            '050' => '050',
            '197' => '197',
            '165' => '165',
            '051' => '051',
            '052' => '052',
            '053' => '053',
            '166' => '166',
            '054' => '054',
            '055' => '055',
            '056' => '056',
            '057' => '057',
            '058' => '058',
            '059' => '059',
            '060' => '060',
            '061' => '061',
            '062' => '062',
            '063' => '063',
            '208' => '208',
            '064' => '064',
            '065' => '065',
            '066' => '066',
            '167' => '167',
            '067' => '067',
            '068' => '068',
            '069' => '069',
            '198' => '198',
            '070' => '070',
            '168' => '168',
            '071' => '071',
            '072' => '072',
            '073' => '073',
            '074' => '074',
            '169' => '169',
            '075' => '075',
            '212' => '212',
            '170' => '170',
            '076' => '076',
            '199' => '199',
            '077' => '077',
            '078' => '078',
            '079' => '079',
            '080' => '080',
            '081' => '081',
            '082' => '082',
            '083' => '083',
            '084' => '084',
            '085' => '085',
            '086' => '086',
            '171' => '171',
            '087' => '087',
            '088' => '088',
            '089' => '089',
            '090' => '090',
            '091' => '091',
            '092' => '092',
            '172' => '172',
            '093' => '093',
            '200' => '200',
            '173' => '173',
            '094' => '094',
            '174' => '174',
            '095' => '095',
            '175' => '175',
            '096' => '096',
            '097' => '097',
            '098' => '098',
            '099' => '099',
            '100' => '100',
            '101' => '101',
            '102' => '102',
            '103' => '103',
            '176' => '176',
            '209' => '209',
            '201' => '201',
            '104' => '104',
            '177' => '177',
            '106' => '106',
            '105' => '105',
            '107' => '107',
            '108' => '108',
            '033' => '033',
            '178' => '178',
            '109' => '109',
            '183' => '183',
            '117' => '117',
            '118' => '118',
            '119' => '119',
            '120' => '120',
            '211' => '211',
            '110' => '110',
            '111' => '111',
            '121' => '121',
            '122' => '122',
            '123' => '123',
            '112' => '112',
            '113' => '113',
            '114' => '114',
            '124' => '124',
            '206' => '206',
            '125' => '125',
            '194' => '194',
            '179' => '179',
            '180' => '180',
            '126' => '126',
            '202' => '202',
            '115' => '115',
            '127' => '127',
            '203' => '203',
            '181' => '181',
            '204' => '204',
            '182' => '182',
            '116' => '116',
            '210' => '210',
            '205' => '205',
            '184' => '184',
            '010' => '010',
            '128' => '128',
            '129' => '129',
            '130' => '130',
            '185' => '185',
            '131' => '131',
            '186' => '186',
            '132' => '132',
            '133' => '133',
            '187' => '187',
            '134' => '134',
            '188' => '188',
            '135' => '135',
            '136' => '136',
            '137' => '137',
            '138' => '138',
            '139' => '139',
            '189' => '189',
            '140' => '140',
            '141' => '141',
            '142' => '142',
            '190' => '190',
            '143' => '143',
            '146' => '146',
            '191' => '191',
            '147' => '147',
            '192' => '192',
            '144' => '144',
            '193' => '193',
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
            ['name' => 'Ajdovščina', 'code' => '001'],
            ['name' => 'Ankaran', 'code' => '213'],
            ['name' => 'Apače', 'code' => '195'],
            ['name' => 'Beltinci', 'code' => '002'],
            ['name' => 'Benedikt', 'code' => '148'],
            ['name' => 'Bistrica ob Sotli', 'code' => '149'],
            ['name' => 'Bled', 'code' => '003'],
            ['name' => 'Bloke', 'code' => '150'],
            ['name' => 'Bohinj', 'code' => '004'],
            ['name' => 'Borovnica', 'code' => '005'],
            ['name' => 'Bovec', 'code' => '006'],
            ['name' => 'Braslovče', 'code' => '151'],
            ['name' => 'Brda', 'code' => '007'],
            ['name' => 'Brežice', 'code' => '009'],
            ['name' => 'Brezovica', 'code' => '008'],
            ['name' => 'Cankova', 'code' => '152'],
            ['name' => 'Celje', 'code' => '011'],
            ['name' => 'Cerklje na Gorenjskem', 'code' => '012'],
            ['name' => 'Cerknica', 'code' => '013'],
            ['name' => 'Cerkno', 'code' => '014'],
            ['name' => 'Cerkvenjak', 'code' => '153'],
            ['name' => 'Cirkulane', 'code' => '196'],
            ['name' => 'Črenšovci', 'code' => '015'],
            ['name' => 'Črna na Koroškem', 'code' => '016'],
            ['name' => 'Črnomelj', 'code' => '017'],
            ['name' => 'Destrnik', 'code' => '018'],
            ['name' => 'Divača', 'code' => '019'],
            ['name' => 'Dobje', 'code' => '154'],
            ['name' => 'Dobrepolje', 'code' => '020'],
            ['name' => 'Dobrna', 'code' => '155'],
            ['name' => 'Dobrova–Polhov Gradec', 'code' => '021'],
            ['name' => 'Dobrovnik', 'code' => '156'],
            ['name' => 'Dol pri Ljubljani', 'code' => '022'],
            ['name' => 'Dolenjske Toplice', 'code' => '157'],
            ['name' => 'Domžale', 'code' => '023'],
            ['name' => 'Dornava', 'code' => '024'],
            ['name' => 'Dravograd', 'code' => '025'],
            ['name' => 'Duplek', 'code' => '026'],
            ['name' => 'Gorenja Vas–Poljane', 'code' => '027'],
            ['name' => 'Gorišnica', 'code' => '028'],
            ['name' => 'Gorje', 'code' => '207'],
            ['name' => 'Gornja Radgona', 'code' => '029'],
            ['name' => 'Gornji Grad', 'code' => '030'],
            ['name' => 'Gornji Petrovci', 'code' => '031'],
            ['name' => 'Grad', 'code' => '158'],
            ['name' => 'Grosuplje', 'code' => '032'],
            ['name' => 'Hajdina', 'code' => '159'],
            ['name' => 'Hoče–Slivnica', 'code' => '160'],
            ['name' => 'Hodoš', 'code' => '161'],
            ['name' => 'Horjul', 'code' => '162'],
            ['name' => 'Hrastnik', 'code' => '034'],
            ['name' => 'Hrpelje–Kozina', 'code' => '035'],
            ['name' => 'Idrija', 'code' => '036'],
            ['name' => 'Ig', 'code' => '037'],
            ['name' => 'Ilirska Bistrica', 'code' => '038'],
            ['name' => 'Ivančna Gorica', 'code' => '039'],
            ['name' => 'Izola', 'code' => '040'],
            ['name' => 'Jesenice', 'code' => '041'],
            ['name' => 'Jezersko', 'code' => '163'],
            ['name' => 'Juršinci', 'code' => '042'],
            ['name' => 'Kamnik', 'code' => '043'],
            ['name' => 'Kanal ob Soči', 'code' => '044'],
            ['name' => 'Kidričevo', 'code' => '045'],
            ['name' => 'Kobarid', 'code' => '046'],
            ['name' => 'Kobilje', 'code' => '047'],
            ['name' => 'Kočevje', 'code' => '048'],
            ['name' => 'Komen', 'code' => '049'],
            ['name' => 'Komenda', 'code' => '164'],
            ['name' => 'Koper', 'code' => '050'],
            ['name' => 'Kostanjevica na Krki', 'code' => '197'],
            ['name' => 'Kostel', 'code' => '165'],
            ['name' => 'Kozje', 'code' => '051'],
            ['name' => 'Kranj', 'code' => '052'],
            ['name' => 'Kranjska Gora', 'code' => '053'],
            ['name' => 'Križevci', 'code' => '166'],
            ['name' => 'Krško', 'code' => '054'],
            ['name' => 'Kungota', 'code' => '055'],
            ['name' => 'Kuzma', 'code' => '056'],
            ['name' => 'Laško', 'code' => '057'],
            ['name' => 'Lenart', 'code' => '058'],
            ['name' => 'Lendava', 'code' => '059'],
            ['name' => 'Litija', 'code' => '060'],
            ['name' => 'Ljubljana', 'code' => '061'],
            ['name' => 'Ljubno', 'code' => '062'],
            ['name' => 'Ljutomer', 'code' => '063'],
            ['name' => 'Log–Dragomer', 'code' => '208'],
            ['name' => 'Logatec', 'code' => '064'],
            ['name' => 'Loška Dolina', 'code' => '065'],
            ['name' => 'Loški Potok', 'code' => '066'],
            ['name' => 'Lovrenc na Pohorju', 'code' => '167'],
            ['name' => 'Luče', 'code' => '067'],
            ['name' => 'Lukovica', 'code' => '068'],
            ['name' => 'Majšperk', 'code' => '069'],
            ['name' => 'Makole', 'code' => '198'],
            ['name' => 'Maribor', 'code' => '070'],
            ['name' => 'Markovci', 'code' => '168'],
            ['name' => 'Medvode', 'code' => '071'],
            ['name' => 'Mengeš', 'code' => '072'],
            ['name' => 'Metlika', 'code' => '073'],
            ['name' => 'Mežica', 'code' => '074'],
            ['name' => 'Miklavž na Dravskem Polju', 'code' => '169'],
            ['name' => 'Miren–Kostanjevica', 'code' => '075'],
            ['name' => 'Mirna', 'code' => '212'],
            ['name' => 'Mirna Peč', 'code' => '170'],
            ['name' => 'Mislinja', 'code' => '076'],
            ['name' => 'Mokronog–Trebelno', 'code' => '199'],
            ['name' => 'Moravče', 'code' => '077'],
            ['name' => 'Moravske Toplice', 'code' => '078'],
            ['name' => 'Mozirje', 'code' => '079'],
            ['name' => 'Murska Sobota', 'code' => '080'],
            ['name' => 'Muta', 'code' => '081'],
            ['name' => 'Naklo', 'code' => '082'],
            ['name' => 'Nazarje', 'code' => '083'],
            ['name' => 'Nova Gorica', 'code' => '084'],
            ['name' => 'Novo Mesto', 'code' => '085'],
            ['name' => 'Odranci', 'code' => '086'],
            ['name' => 'Oplotnica', 'code' => '171'],
            ['name' => 'Ormož', 'code' => '087'],
            ['name' => 'Osilnica', 'code' => '088'],
            ['name' => 'Pesnica', 'code' => '089'],
            ['name' => 'Piran', 'code' => '090'],
            ['name' => 'Pivka', 'code' => '091'],
            ['name' => 'Podčetrtek', 'code' => '092'],
            ['name' => 'Podlehnik', 'code' => '172'],
            ['name' => 'Podvelka', 'code' => '093'],
            ['name' => 'Poljčane', 'code' => '200'],
            ['name' => 'Polzela', 'code' => '173'],
            ['name' => 'Postojna', 'code' => '094'],
            ['name' => 'Prebold', 'code' => '174'],
            ['name' => 'Preddvor', 'code' => '095'],
            ['name' => 'Prevalje', 'code' => '175'],
            ['name' => 'Ptuj', 'code' => '096'],
            ['name' => 'Puconci', 'code' => '097'],
            ['name' => 'Rače–Fram', 'code' => '098'],
            ['name' => 'Radeče', 'code' => '099'],
            ['name' => 'Radenci', 'code' => '100'],
            ['name' => 'Radlje ob Dravi', 'code' => '101'],
            ['name' => 'Radovljica', 'code' => '102'],
            ['name' => 'Ravne na Koroškem', 'code' => '103'],
            ['name' => 'Razkrižje', 'code' => '176'],
            ['name' => 'Rečica ob Savinji', 'code' => '209'],
            ['name' => 'Renče–Vogrsko', 'code' => '201'],
            ['name' => 'Ribnica', 'code' => '104'],
            ['name' => 'Ribnica na Pohorju', 'code' => '177'],
            ['name' => 'Rogaška Slatina', 'code' => '106'],
            ['name' => 'Rogašovci', 'code' => '105'],
            ['name' => 'Rogatec', 'code' => '107'],
            ['name' => 'Ruše', 'code' => '108'],
            ['name' => 'Šalovci', 'code' => '033'],
            ['name' => 'Selnica ob Dravi', 'code' => '178'],
            ['name' => 'Semič', 'code' => '109'],
            ['name' => 'Šempeter–Vrtojba', 'code' => '183'],
            ['name' => 'Šenčur', 'code' => '117'],
            ['name' => 'Šentilj', 'code' => '118'],
            ['name' => 'Šentjernej', 'code' => '119'],
            ['name' => 'Šentjur', 'code' => '120'],
            ['name' => 'Šentrupert', 'code' => '211'],
            ['name' => 'Sevnica', 'code' => '110'],
            ['name' => 'Sežana', 'code' => '111'],
            ['name' => 'Škocjan', 'code' => '121'],
            ['name' => 'Škofja Loka', 'code' => '122'],
            ['name' => 'Škofljica', 'code' => '123'],
            ['name' => 'Slovenj Gradec', 'code' => '112'],
            ['name' => 'Slovenska Bistrica', 'code' => '113'],
            ['name' => 'Slovenske Konjice', 'code' => '114'],
            ['name' => 'Šmarje pri Jelšah', 'code' => '124'],
            ['name' => 'Šmarješke Toplice', 'code' => '206'],
            ['name' => 'Šmartno ob Paki', 'code' => '125'],
            ['name' => 'Šmartno pri Litiji', 'code' => '194'],
            ['name' => 'Sodražica', 'code' => '179'],
            ['name' => 'Solčava', 'code' => '180'],
            ['name' => 'Šoštanj', 'code' => '126'],
            ['name' => 'Središče ob Dravi', 'code' => '202'],
            ['name' => 'Starše', 'code' => '115'],
            ['name' => 'Štore', 'code' => '127'],
            ['name' => 'Straža', 'code' => '203'],
            ['name' => 'Sveta Ana', 'code' => '181'],
            ['name' => 'Sveta Trojica v Slovenskih Goricah', 'code' => '204'],
            ['name' => 'Sveti Andraž v Slovenskih Goricah', 'code' => '182'],
            ['name' => 'Sveti Jurij ob Ščavnici', 'code' => '116'],
            ['name' => 'Sveti Jurij v Slovenskih Goricah', 'code' => '210'],
            ['name' => 'Sveti Tomaž', 'code' => '205'],
            ['name' => 'Tabor', 'code' => '184'],
            ['name' => 'Tišina', 'code' => '010'],
            ['name' => 'Tolmin', 'code' => '128'],
            ['name' => 'Trbovlje', 'code' => '129'],
            ['name' => 'Trebnje', 'code' => '130'],
            ['name' => 'Trnovska Vas', 'code' => '185'],
            ['name' => 'Tržič', 'code' => '131'],
            ['name' => 'Trzin', 'code' => '186'],
            ['name' => 'Turnišče', 'code' => '132'],
            ['name' => 'Velenje', 'code' => '133'],
            ['name' => 'Velika Polana', 'code' => '187'],
            ['name' => 'Velike Lašče', 'code' => '134'],
            ['name' => 'Veržej', 'code' => '188'],
            ['name' => 'Videm', 'code' => '135'],
            ['name' => 'Vipava', 'code' => '136'],
            ['name' => 'Vitanje', 'code' => '137'],
            ['name' => 'Vodice', 'code' => '138'],
            ['name' => 'Vojnik', 'code' => '139'],
            ['name' => 'Vransko', 'code' => '189'],
            ['name' => 'Vrhnika', 'code' => '140'],
            ['name' => 'Vuzenica', 'code' => '141'],
            ['name' => 'Zagorje ob Savi', 'code' => '142'],
            ['name' => 'Žalec', 'code' => '190'],
            ['name' => 'Zavrč', 'code' => '143'],
            ['name' => 'Železniki', 'code' => '146'],
            ['name' => 'Žetale', 'code' => '191'],
            ['name' => 'Žiri', 'code' => '147'],
            ['name' => 'Žirovnica', 'code' => '192'],
            ['name' => 'Zreče', 'code' => '144'],
            ['name' => 'Žužemberk', 'code' => '193'],
        ];
    }
}
