<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\NorthMacedonia;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class NorthMacedoniaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_north_macedonia_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.north_macedonia';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'MK';
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
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'MK', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/north-macedonia-address-areas.csv',
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
            '801' => '801',
            '802' => '802',
            '201' => '201',
            '501' => '501',
            '401' => '401',
            '601' => '601',
            '402' => '402',
            '602' => '602',
            '803' => '803',
            '815' => '815',
            '109' => '109',
            '814' => '814',
            '313' => '313',
            '210' => '210',
            '816' => '816',
            '303' => '303',
            '304' => '304',
            '203' => '203',
            '502' => '502',
            '103' => '103',
            '406' => '406',
            '503' => '503',
            '804' => '804',
            '405' => '405',
            '805' => '805',
            '604' => '604',
            '102' => '102',
            '807' => '807',
            '606' => '606',
            '205' => '205',
            '808' => '808',
            '104' => '104',
            '307' => '307',
            '809' => '809',
            '206' => '206',
            '407' => '407',
            '701' => '701',
            '702' => '702',
            '504' => '504',
            '505' => '505',
            '703' => '703',
            '704' => '704',
            '105' => '105',
            '207' => '207',
            '308' => '308',
            '607' => '607',
            '506' => '506',
            '106' => '106',
            '507' => '507',
            '408' => '408',
            '310' => '310',
            '208' => '208',
            '810' => '810',
            '311' => '311',
            '508' => '508',
            '209' => '209',
            '409' => '409',
            '705' => '705',
            '509' => '509',
            '107' => '107',
            '811' => '811',
            '812' => '812',
            '706' => '706',
            '211' => '211',
            '312' => '312',
            '410' => '410',
            '813' => '813',
            '817' => '817',
            '108' => '108',
            '608' => '608',
            '609' => '609',
            '403' => '403',
            '404' => '404',
            '101' => '101',
            '301' => '301',
            '202' => '202',
            '603' => '603',
            '806' => '806',
            '605' => '605',
            '204' => '204',
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
            ['name' => 'Aerodrom', 'code' => '801'],
            ['name' => 'Aračinovo', 'code' => '802'],
            ['name' => 'Berovo', 'code' => '201'],
            ['name' => 'Bitola', 'code' => '501'],
            ['name' => 'Bogdanci', 'code' => '401'],
            ['name' => 'Bogovinje', 'code' => '601'],
            ['name' => 'Bosilovo', 'code' => '402'],
            ['name' => 'Brvenica', 'code' => '602'],
            ['name' => 'Butel', 'code' => '803'],
            ['name' => 'Čair', 'code' => '815'],
            ['name' => 'Čaška', 'code' => '109'],
            ['name' => 'Centar', 'code' => '814'],
            ['name' => 'Centar Župa', 'code' => '313'],
            ['name' => 'Češinovo-Obleševo', 'code' => '210'],
            ['name' => 'Čučer-Sandevo', 'code' => '816'],
            ['name' => 'Debar', 'code' => '303'],
            ['name' => 'Debarca', 'code' => '304'],
            ['name' => 'Delčevo', 'code' => '203'],
            ['name' => 'Demir Hisar', 'code' => '502'],
            ['name' => 'Demir Kapija', 'code' => '103'],
            ['name' => 'Dojran', 'code' => '406'],
            ['name' => 'Dolneni', 'code' => '503'],
            ['name' => 'Gazi Baba', 'code' => '804'],
            ['name' => 'Gevgelija', 'code' => '405'],
            ['name' => 'Gjorče Petrov', 'code' => '805'],
            ['name' => 'Gostivar', 'code' => '604'],
            ['name' => 'Gradsko', 'code' => '102'],
            ['name' => 'Ilinden', 'code' => '807'],
            ['name' => 'Jegunovce', 'code' => '606'],
            ['name' => 'Karbinci', 'code' => '205'],
            ['name' => 'Karpoš', 'code' => '808'],
            ['name' => 'Kavadarci', 'code' => '104'],
            ['name' => 'Kičevo', 'code' => '307'],
            ['name' => 'Kisela Voda', 'code' => '809'],
            ['name' => 'Kočani', 'code' => '206'],
            ['name' => 'Konče', 'code' => '407'],
            ['name' => 'Kratovo', 'code' => '701'],
            ['name' => 'Kriva Palanka', 'code' => '702'],
            ['name' => 'Krivogaštani', 'code' => '504'],
            ['name' => 'Kruševo', 'code' => '505'],
            ['name' => 'Kumanovo', 'code' => '703'],
            ['name' => 'Lipkovo', 'code' => '704'],
            ['name' => 'Lozovo', 'code' => '105'],
            ['name' => 'Makedonska Kamenica', 'code' => '207'],
            ['name' => 'Makedonski Brod', 'code' => '308'],
            ['name' => 'Mavrovo and Rostuša', 'code' => '607'],
            ['name' => 'Mogila', 'code' => '506'],
            ['name' => 'Negotino', 'code' => '106'],
            ['name' => 'Novaci', 'code' => '507'],
            ['name' => 'Novo Selo', 'code' => '408'],
            ['name' => 'Ohrid', 'code' => '310'],
            ['name' => 'Pehčevo', 'code' => '208'],
            ['name' => 'Petrovec', 'code' => '810'],
            ['name' => 'Plasnica', 'code' => '311'],
            ['name' => 'Prilep', 'code' => '508'],
            ['name' => 'Probištip', 'code' => '209'],
            ['name' => 'Radoviš', 'code' => '409'],
            ['name' => 'Rankovce', 'code' => '705'],
            ['name' => 'Resen', 'code' => '509'],
            ['name' => 'Rosoman', 'code' => '107'],
            ['name' => 'Saraj', 'code' => '811'],
            ['name' => 'Sopište', 'code' => '812'],
            ['name' => 'Staro Nagoričane', 'code' => '706'],
            ['name' => 'Štip', 'code' => '211'],
            ['name' => 'Struga', 'code' => '312'],
            ['name' => 'Strumica', 'code' => '410'],
            ['name' => 'Studeničani', 'code' => '813'],
            ['name' => 'Šuto Orizari', 'code' => '817'],
            ['name' => 'Sveti Nikole', 'code' => '108'],
            ['name' => 'Tearce', 'code' => '608'],
            ['name' => 'Tetovo', 'code' => '609'],
            ['name' => 'Valandovo', 'code' => '403'],
            ['name' => 'Vasilevo', 'code' => '404'],
            ['name' => 'Veles', 'code' => '101'],
            ['name' => 'Vevčani', 'code' => '301'],
            ['name' => 'Vinica', 'code' => '202'],
            ['name' => 'Vrapčište', 'code' => '603'],
            ['name' => 'Zelenikovo', 'code' => '806'],
            ['name' => 'Želino', 'code' => '605'],
            ['name' => 'Zrnovci', 'code' => '204'],
        ];
    }
}
