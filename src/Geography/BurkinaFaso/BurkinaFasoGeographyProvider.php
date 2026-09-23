<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\BurkinaFaso;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryAreaTypeLabelProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class BurkinaFasoGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_burkina_faso_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.burkina_faso';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'BF';
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
                        label: 'Region',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['region'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'province',
                        label: 'Province',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['province'],
                        areaLevels: [2],
                        parentKey: 'region',
                        assignmentRole: 'province',
                    ),
                ],
            ),
        ];
    }

    /** @return array<string, string> */
    public function areaTypeLabels(): array
    {
        // French is the administrative language; the reform tiers are régions and provinces.
        return [
            'region' => 'Région',
            'province' => 'Province',
        ];
    }

    /** @return list<array{state_code: string, type_labels: array<string, string>}> */
    public function stateAreaTypeLabels(): array
    {
        return [];
    }

    /** @return array<string, list<array{role: string, country_code?: string, is_primary?: bool}>> */
    public function areaRoles(AddressCountry $country): array
    {
        $roles = [];

        foreach ($this->addressAreaSource()->areas() as $area) {
            $areaRoles = match ($area->type) {
                'region' => ['region'],
                'province' => ['province'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'BF', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/burkina-faso-address-areas.csv',
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
            'BAL' => 'BAL',
            'BAM' => 'BAM',
            '01' => '01',
            'BAN' => 'BAN',
            'OUB' => 'OUB',
            'BAZ' => 'BAZ',
            'BGR' => 'BGR',
            'BLG' => 'BLG',
            'BLK' => 'BLK',
            'COM' => 'COM',
            'SOM' => 'SOM',
            '13' => '13',
            'DYA' => 'DYA',
            'GAN' => 'GAN',
            'GNA' => 'GNA',
            'TAP' => 'TAP',
            '08' => '08',
            'GOU' => 'GOU',
            '09' => '09',
            'HOU' => 'HOU',
            'IOB' => 'IOB',
            '03' => '03',
            'KAD' => 'KAD',
            'KAR' => 'KAR',
            'KEN' => 'KEN',
            'KMD' => 'KMD',
            'KMP' => 'KMP',
            'KOS' => 'KOS',
            'KOP' => 'KOP',
            'KOT' => 'KOT',
            'KOW' => 'KOW',
            '05' => '05',
            '12' => '12',
            'LER' => 'LER',
            'LOR' => 'LOR',
            'MOU' => 'MOU',
            'NAO' => 'NAO',
            '04' => '04',
            'NAM' => 'NAM',
            '06' => '06',
            'NAY' => 'NAY',
            '07' => '07',
            'NOU' => 'NOU',
            '11' => '11',
            'OUD' => 'OUD',
            'PAS' => 'PAS',
            'PON' => 'PON',
            'SMT' => 'SMT',
            'SNG' => 'SNG',
            '14' => '14',
            'SEN' => 'SEN',
            'SIS' => 'SIS',
            '15' => '15',
            '16' => '16',
            'SOR' => 'SOR',
            '02' => '02',
            '17' => '17',
            'TUI' => 'TUI',
            '10' => '10',
            'YAG' => 'YAG',
            'YAT' => 'YAT',
            'ZIR' => 'ZIR',
            'ZON' => 'ZON',
            'ZOU' => 'ZOU',
        ];

        $regionCodes = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17'];

        return array_map(
            static fn (string $areaCode): array => [
                'area_code' => $areaCode,
                'source' => self::AREA_SOURCE,
                'area_level' => in_array($areaCode, $regionCodes, true) ? 1 : 2,
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
            ['name' => 'Balé', 'code' => 'BAL'],
            ['name' => 'Bam', 'code' => 'BAM'],
            ['name' => 'Bankui', 'code' => '01'],
            ['name' => 'Banwa', 'code' => 'BAN'],
            ['name' => 'Bassitenga', 'code' => 'OUB'],
            ['name' => 'Bazèga', 'code' => 'BAZ'],
            ['name' => 'Bougouriba', 'code' => 'BGR'],
            ['name' => 'Boulgou', 'code' => 'BLG'],
            ['name' => 'Boulkiemde', 'code' => 'BLK'],
            ['name' => 'Comoé', 'code' => 'COM'],
            ['name' => 'Djelgodji', 'code' => 'SOM'],
            ['name' => 'Djôrô', 'code' => '13'],
            ['name' => 'Dyamongou', 'code' => 'DYA'],
            ['name' => 'Ganzourgou', 'code' => 'GAN'],
            ['name' => 'Gnagna', 'code' => 'GNA'],
            ['name' => 'Gobnangou', 'code' => 'TAP'],
            ['name' => 'Goulmou', 'code' => '08'],
            ['name' => 'Gourma', 'code' => 'GOU'],
            ['name' => 'Guiriko', 'code' => '09'],
            ['name' => 'Houet', 'code' => 'HOU'],
            ['name' => 'Ioba', 'code' => 'IOB'],
            ['name' => 'Kadiogo', 'code' => '03'],
            ['name' => 'Kadiogo', 'code' => 'KAD'],
            ['name' => 'Karo-Peli', 'code' => 'KAR'],
            ['name' => 'Kénédougou', 'code' => 'KEN'],
            ['name' => 'Komondjari', 'code' => 'KMD'],
            ['name' => 'Kompienga', 'code' => 'KMP'],
            ['name' => 'Koosin', 'code' => 'KOS'],
            ['name' => 'Koulpélogo', 'code' => 'KOP'],
            ['name' => 'Kouritenga', 'code' => 'KOT'],
            ['name' => 'Kourwéogo', 'code' => 'KOW'],
            ['name' => 'Kuilsé', 'code' => '05'],
            ['name' => 'Liptako', 'code' => '12'],
            ['name' => 'Léraba', 'code' => 'LER'],
            ['name' => 'Loroum', 'code' => 'LOR'],
            ['name' => 'Mouhoun', 'code' => 'MOU'],
            ['name' => 'Nahouri', 'code' => 'NAO'],
            ['name' => 'Nakambé', 'code' => '04'],
            ['name' => 'Namentenga', 'code' => 'NAM'],
            ['name' => 'Nando', 'code' => '06'],
            ['name' => 'Nayala', 'code' => 'NAY'],
            ['name' => 'Nazinon', 'code' => '07'],
            ['name' => 'Noumbiel', 'code' => 'NOU'],
            ['name' => 'Oubri', 'code' => '11'],
            ['name' => 'Oudalan', 'code' => 'OUD'],
            ['name' => 'Passoré', 'code' => 'PAS'],
            ['name' => 'Poni', 'code' => 'PON'],
            ['name' => 'Sandbondtenga', 'code' => 'SMT'],
            ['name' => 'Sanguié', 'code' => 'SNG'],
            ['name' => 'Sirba', 'code' => '14'],
            ['name' => 'Séno', 'code' => 'SEN'],
            ['name' => 'Sissili', 'code' => 'SIS'],
            ['name' => 'Soum', 'code' => '15'],
            ['name' => 'Sourou', 'code' => '16'],
            ['name' => 'Sourou', 'code' => 'SOR'],
            ['name' => 'Tannounyan', 'code' => '02'],
            ['name' => 'Tapoa', 'code' => '17'],
            ['name' => 'Tuy', 'code' => 'TUI'],
            ['name' => 'Yaadga', 'code' => '10'],
            ['name' => 'Yagha', 'code' => 'YAG'],
            ['name' => 'Yatenga', 'code' => 'YAT'],
            ['name' => 'Ziro', 'code' => 'ZIR'],
            ['name' => 'Zondoma', 'code' => 'ZON'],
            ['name' => 'Zoundwéogo', 'code' => 'ZOU'],
        ];
    }
}
