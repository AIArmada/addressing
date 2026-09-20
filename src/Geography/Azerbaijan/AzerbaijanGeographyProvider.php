<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Azerbaijan;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class AzerbaijanGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_azerbaijan_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.azerbaijan';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'AZ';
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
                        key: 'district',
                        label: 'District / Municipality / Autonomous Republic',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['district', 'municipality', 'autonomous_republic'],
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
                'district' => ['district'],
                'municipality' => ['municipality'],
                'autonomous_republic' => ['autonomous_republic'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'AZ', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/azerbaijan-address-areas.csv',
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
            'ABS' => 'ABS',
            'AGM' => 'AGM',
            'AGS' => 'AGS',
            'AGC' => 'AGC',
            'AGA' => 'AGA',
            'AGU' => 'AGU',
            'AST' => 'AST',
            'BAB' => 'BAB',
            'BA' => 'BA',
            'BAL' => 'BAL',
            'BAR' => 'BAR',
            'BEY' => 'BEY',
            'BIL' => 'BIL',
            'DAS' => 'DAS',
            'FUZ' => 'FUZ',
            'GA' => 'GA',
            'GAD' => 'GAD',
            'QOB' => 'QOB',
            'GOR' => 'GOR',
            'GOY' => 'GOY',
            'GYG' => 'GYG',
            'HAC' => 'HAC',
            'IMI' => 'IMI',
            'ISM' => 'ISM',
            'CAB' => 'CAB',
            'CAL' => 'CAL',
            'CUL' => 'CUL',
            'KAL' => 'KAL',
            'KAN' => 'KAN',
            'XAC' => 'XAC',
            'XA' => 'XA',
            'XIZ' => 'XIZ',
            'XCI' => 'XCI',
            'KUR' => 'KUR',
            'LAC' => 'LAC',
            'LAN' => 'LAN',
            'LA' => 'LA',
            'LER' => 'LER',
            'XVD' => 'XVD',
            'MAS' => 'MAS',
            'MI' => 'MI',
            'NA' => 'NA',
            'NV' => 'NV',
            'NX' => 'NX',
            'NEF' => 'NEF',
            'OGU' => 'OGU',
            'ORD' => 'ORD',
            'QAB' => 'QAB',
            'QAX' => 'QAX',
            'QAZ' => 'QAZ',
            'QBA' => 'QBA',
            'QBI' => 'QBI',
            'QUS' => 'QUS',
            'SAT' => 'SAT',
            'SAB' => 'SAB',
            'SAD' => 'SAD',
            'SAL' => 'SAL',
            'SMX' => 'SMX',
            'SBN' => 'SBN',
            'SAH' => 'SAH',
            'SA' => 'SA',
            'SAK' => 'SAK',
            'SMI' => 'SMI',
            'SKR' => 'SKR',
            'SAR' => 'SAR',
            'SR' => 'SR',
            'SUS' => 'SUS',
            'SIY' => 'SIY',
            'SM' => 'SM',
            'TAR' => 'TAR',
            'TOV' => 'TOV',
            'UCA' => 'UCA',
            'YAR' => 'YAR',
            'YEV' => 'YEV',
            'YE' => 'YE',
            'ZAN' => 'ZAN',
            'ZAQ' => 'ZAQ',
            'ZAR' => 'ZAR',
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
            ['name' => 'Absheron', 'code' => 'ABS'],
            ['name' => 'Agdam', 'code' => 'AGM'],
            ['name' => 'Agdash', 'code' => 'AGS'],
            ['name' => 'Aghjabadi', 'code' => 'AGC'],
            ['name' => 'Agstafa', 'code' => 'AGA'],
            ['name' => 'Agsu', 'code' => 'AGU'],
            ['name' => 'Astara', 'code' => 'AST'],
            ['name' => 'Babek', 'code' => 'BAB'],
            ['name' => 'Baku', 'code' => 'BA'],
            ['name' => 'Balakan', 'code' => 'BAL'],
            ['name' => 'Barda', 'code' => 'BAR'],
            ['name' => 'Beylagan', 'code' => 'BEY'],
            ['name' => 'Bilasuvar', 'code' => 'BIL'],
            ['name' => 'Dashkasan', 'code' => 'DAS'],
            ['name' => 'Fizuli', 'code' => 'FUZ'],
            ['name' => 'Ganja', 'code' => 'GA'],
            ['name' => 'Gədəbəy', 'code' => 'GAD'],
            ['name' => 'Gobustan', 'code' => 'QOB'],
            ['name' => 'Goranboy', 'code' => 'GOR'],
            ['name' => 'Goychay', 'code' => 'GOY'],
            ['name' => 'Goygol', 'code' => 'GYG'],
            ['name' => 'Hajigabul', 'code' => 'HAC'],
            ['name' => 'Imishli', 'code' => 'IMI'],
            ['name' => 'Ismailli', 'code' => 'ISM'],
            ['name' => 'Jabrayil', 'code' => 'CAB'],
            ['name' => 'Jalilabad', 'code' => 'CAL'],
            ['name' => 'Julfa', 'code' => 'CUL'],
            ['name' => 'Kalbajar', 'code' => 'KAL'],
            ['name' => 'Kangarli', 'code' => 'KAN'],
            ['name' => 'Khachmaz', 'code' => 'XAC'],
            ['name' => 'Khankendi', 'code' => 'XA'],
            ['name' => 'Khizi', 'code' => 'XIZ'],
            ['name' => 'Khojali', 'code' => 'XCI'],
            ['name' => 'Kurdamir', 'code' => 'KUR'],
            ['name' => 'Lachin', 'code' => 'LAC'],
            ['name' => 'Lankaran', 'code' => 'LAN'],
            ['name' => 'Lankaran', 'code' => 'LA'],
            ['name' => 'Lerik', 'code' => 'LER'],
            ['name' => 'Martuni', 'code' => 'XVD'],
            ['name' => 'Masally', 'code' => 'MAS'],
            ['name' => 'Mingachevir', 'code' => 'MI'],
            ['name' => 'Naftalan', 'code' => 'NA'],
            ['name' => 'Nakhchivan', 'code' => 'NV'],
            ['name' => 'Nakhchivan', 'code' => 'NX'],
            ['name' => 'Neftchala', 'code' => 'NEF'],
            ['name' => 'Oghuz', 'code' => 'OGU'],
            ['name' => 'Ordubad', 'code' => 'ORD'],
            ['name' => 'Qabala', 'code' => 'QAB'],
            ['name' => 'Qakh', 'code' => 'QAX'],
            ['name' => 'Qazakh', 'code' => 'QAZ'],
            ['name' => 'Quba', 'code' => 'QBA'],
            ['name' => 'Qubadli', 'code' => 'QBI'],
            ['name' => 'Qusar', 'code' => 'QUS'],
            ['name' => 'Saatly', 'code' => 'SAT'],
            ['name' => 'Sabirabad', 'code' => 'SAB'],
            ['name' => 'Sadarak', 'code' => 'SAD'],
            ['name' => 'Salyan', 'code' => 'SAL'],
            ['name' => 'Samukh', 'code' => 'SMX'],
            ['name' => 'Shabran', 'code' => 'SBN'],
            ['name' => 'Shahbuz', 'code' => 'SAH'],
            ['name' => 'Shaki', 'code' => 'SA'],
            ['name' => 'Shaki', 'code' => 'SAK'],
            ['name' => 'Shamakhi', 'code' => 'SMI'],
            ['name' => 'Shamkir', 'code' => 'SKR'],
            ['name' => 'Sharur', 'code' => 'SAR'],
            ['name' => 'Shirvan', 'code' => 'SR'],
            ['name' => 'Shusha', 'code' => 'SUS'],
            ['name' => 'Siazan', 'code' => 'SIY'],
            ['name' => 'Sumqayit', 'code' => 'SM'],
            ['name' => 'Tartar', 'code' => 'TAR'],
            ['name' => 'Tovuz', 'code' => 'TOV'],
            ['name' => 'Ujar', 'code' => 'UCA'],
            ['name' => 'Yardymli', 'code' => 'YAR'],
            ['name' => 'Yevlakh', 'code' => 'YEV'],
            ['name' => 'Yevlakh', 'code' => 'YE'],
            ['name' => 'Zangilan', 'code' => 'ZAN'],
            ['name' => 'Zaqatala', 'code' => 'ZAQ'],
            ['name' => 'Zardab', 'code' => 'ZAR'],
        ];
    }
}
