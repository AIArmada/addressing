<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Malaysia;

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

class MalaysiaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    private const string AREA_SOURCE = 'aiarmada_addressing_malaysia_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.malaysia';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'MY';
    }

    public function seed(AddressCountry $malaysia): void
    {
        $stateClass = ModelResolver::stateClass();
        $statesData = $this->stateDefinitions();

        foreach ($statesData as $s) {
            $stateClass::updateOrCreate(
                ['country_id' => $malaysia->id, 'code' => $s['code']],
                [
                    'name' => $s['name'],
                    'country_code' => $this->countryCode(),
                ],
            );
        }
    }

    /**
     * Administrative geography is the primary hierarchy (listed first);
     * postal localities are the secondary delivery overlay.
     *
     * @return list<AddressHierarchyDefinition>
     */
    public function addressHierarchies(): array
    {
        return [
            new AddressHierarchyDefinition(
                key: 'administrative',
                label: 'Administrative / Land Geography',
                levels: [
                    new AddressLevelDefinition(
                        key: 'region',
                        label: 'State / Federal Territory',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['state', 'wilayah_persekutuan'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'division',
                        label: 'Division / Bahagian',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['division'],
                        areaLevel: 2,
                        parentKey: 'region',
                        assignmentRole: 'administrative_division',
                    ),
                    new AddressLevelDefinition(
                        key: 'district',
                        label: 'District / Jajahan / Jajahan Kecil',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['district', 'minor_district'],
                        areaLevels: [2, 3],
                        parentKey: 'region',
                        assignmentRole: 'administrative_district',
                    ),
                    new AddressLevelDefinition(
                        key: 'subdivision',
                        label: 'Mukim / Subdistrict / Bandar / Pekan',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['city', 'municipality', 'mukim', 'subdistrict', 'bandar', 'pekan'],
                        areaLevels: [2, 3, 4],
                        parentKey: 'region',
                        assignmentRole: 'administrative_subdivision',
                    ),
                ],
            ),
            new AddressHierarchyDefinition(
                key: 'postal',
                label: 'Postal / Address Geography',
                levels: [
                    new AddressLevelDefinition(
                        key: 'region',
                        label: 'State / Federal Territory',
                        kind: 'state',
                        hierarchyType: 'postal',
                        areaTypes: ['state', 'wilayah_persekutuan'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'locality',
                        label: 'Locality / Precinct / Kampung',
                        kind: 'area',
                        hierarchyType: 'postal',
                        areaTypes: ['locality', 'precinct'],
                        areaLevels: [2, 3, 4],
                        parentKey: 'region',
                        assignmentRole: 'postal_locality',
                        refinedBy: 'administrative_district',
                    ),
                ],
            ),
        ];
    }

    /** @return array<string, string> */
    public function areaTypeLabels(): array
    {
        // Headline rendering covers every Malaysian type (Mukim, Bandar,
        // Pekan, Precinct); only state-specific proper terms are declared.
        return [];
    }

    /** @return list<array{state_code: string, type_labels: array<string, string>}> */
    public function stateAreaTypeLabels(): array
    {
        return [
            ['state_code' => '03', 'type_labels' => ['district' => 'Jajahan', 'minor_district' => 'Jajahan Kecil']],
            ['state_code' => '06', 'type_labels' => ['minor_district' => 'Daerah Kecil']],
        ];
    }

    /** @return array<string, list<array{role: string, country_code?: string, is_primary?: bool}>> */
    public function areaRoles(AddressCountry $country): array
    {
        $roles = [];

        foreach ($this->addressAreaSource()->areas() as $area) {
            $areaRoles = match ($area->type) {
                'state', 'wilayah_persekutuan' => ['region'],
                'division' => ['administrative_division'],
                'district', 'minor_district' => ['administrative_district'],
                'city', 'municipality', 'mukim', 'subdistrict', 'bandar', 'pekan' => ['administrative_subdivision'],
                'precinct', 'locality' => ['postal_locality'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'MY', 'is_primary' => true],
                $areaRoles,
            );
        }

        return $roles;
    }

    /** @return array<string, list<array{name: string, name_type?: string, is_preferred?: bool}>> */
    public function areaNames(AddressCountry $country): array
    {
        return [
            'my:state:wilayah-persekutuan-kuala-lumpur' => [
                ['name' => 'Kuala Lumpur', 'name_type' => 'common', 'is_preferred' => true],
                ['name' => 'KL', 'name_type' => 'abbreviation'],
            ],
            'my:state:wilayah-persekutuan-putrajaya' => [
                ['name' => 'Putrajaya', 'name_type' => 'common', 'is_preferred' => true],
            ],
            'my:state:wilayah-persekutuan-labuan' => [
                ['name' => 'Labuan', 'name_type' => 'common', 'is_preferred' => true],
            ],
            // JUPEM UPI spelling; the rows use the gazette/KWP "Hulu Klang" form.
            'my:subdistrict:district:selangor:gombak:hulu-klang' => [
                ['name' => 'Hulu Kelang', 'name_type' => 'alternative'],
                ['name' => 'Ulu Kelang', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:state:wilayah-persekutuan-kuala-lumpur:mukim-hulu-klang' => [
                ['name' => 'Hulu Kelang', 'name_type' => 'alternative'],
                ['name' => 'Ulu Kelang', 'name_type' => 'alternative'],
            ],
            // Gazetted names; rows keep the long-established common spellings.
            'my:subdistrict:district:selangor:hulu-langat:hulu-langat' => [
                ['name' => 'Ulu Langat', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:selangor:hulu-langat:hulu-semenyih' => [
                ['name' => 'Ulu Semenyih', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:selangor:hulu-selangor:hulu-bernam' => [
                ['name' => 'Ulu Bernam', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:selangor:hulu-selangor:hulu-yam' => [
                ['name' => 'Ulu Yam', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:selangor:hulu-selangor:kuala-kubu-bharu' => [
                ['name' => 'Kuala Kubu Baharu', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:selangor:kuala-langat:telok-panglima-garang' => [
                ['name' => 'Teluk Panglima Garang', 'name_type' => 'alternative'],
            ],
            // Common names for rows renamed to their gazetted UPI form.
            'my:subdistrict:district:selangor:klang:port-swettenham' => [
                ['name' => 'Port Klang', 'name_type' => 'common', 'is_preferred' => true],
            ],
            'my:subdistrict:district:selangor:gombak:gombak-setia' => [
                ['name' => 'Gombak', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:selangor:sepang:baru-salak-tinggi' => [
                ['name' => 'Salak Tinggi', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:selangor:kuala-langat:tanjong-sepat' => [
                ['name' => 'Tanjung Sepat', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:pahang:genting:genting' => [
                ['name' => 'Genting Highlands', 'name_type' => 'common', 'is_preferred' => true],
            ],
            'my:subdistrict:district:pahang:temerloh:kuala-kerau' => [
                ['name' => 'Kuala Krau', 'name_type' => 'common', 'is_preferred' => true],
            ],
            // JUPEM UPI spellings; rows keep the common Hulu forms.
            'my:subdistrict:district:pahang:jelai:ulu-jelai' => [
                ['name' => 'Hulu Jelai', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:pahang:kuantan:hulu-kuantan' => [
                ['name' => 'Ulu Kuantan', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:pahang:kuantan:hulu-lepar' => [
                ['name' => 'Ulu Lepar', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:pahang:jerantut:hulu-cheka' => [
                ['name' => 'Ulu Cheka', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:pahang:jerantut:hulu-tembeling' => [
                ['name' => 'Ulu Tembeling', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:pahang:raub:hulu-dong' => [
                ['name' => 'Ulu Dong', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:pahang:cameron-highlands:hulu-telom' => [
                ['name' => 'Ulu Telom', 'name_type' => 'alternative'],
            ],
            // JUPEM UPI spellings for Johor rows keeping common forms.
            'my:subdistrict:district:johor:kluang:niyor' => [
                ['name' => 'Nyior', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:johor:kota-tinggi:ulu-sungai-sedili-besar' => [
                ['name' => 'Ulu Sungei Sedili Besar', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:johor:pontian:sungai-pinggan' => [
                ['name' => 'Sungei Pinggan', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:johor:kluang:rengam' => [
                ['name' => 'Renggam', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:johor:tangkak:grisek' => [
                ['name' => 'Gerisek', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:johor:pontian:pontian-kechil' => [
                ['name' => 'Bandar Pontian', 'name_type' => 'alternative'],
            ],
            // JUPEM UPI spellings for Melaka rows keeping common forms.
            'my:subdistrict:district:melaka:melaka-tengah:sungai-udang' => [
                ['name' => 'Sungei Udang', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:melaka:jasin:sungai-rambai' => [
                ['name' => 'Sungei Rambai', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:melaka:alor-gajah:kuala-sungai-baru' => [
                ['name' => 'Kuala Sungei Baru', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:melaka:alor-gajah:sungai-buloh' => [
                ['name' => 'Sungei Buloh', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:melaka:alor-gajah:sungai-petai' => [
                ['name' => 'Sungei Petai', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:melaka:alor-gajah:sungai-siput' => [
                ['name' => 'Sungei Siput', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:melaka:alor-gajah:sungai-baru-tengah' => [
                ['name' => 'Sungei Baru', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:melaka:alor-gajah:sungai-baru-ilir' => [
                ['name' => 'Sungei Baru Ilir', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:melaka:alor-gajah:sungai-baru-ulu' => [
                ['name' => 'Sungei Baru Ulu', 'name_type' => 'alternative'],
            ],
            // JUPEM UPI spellings for Penang rows keeping common forms.
            'my:subdistrict:district:pulau-pinang:timur-laut:air-itam' => [
                ['name' => 'Ayer Itam', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:pulau-pinang:timur-laut:batu-ferringhi' => [
                ['name' => 'Batu Feringgi', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:pulau-pinang:timur-laut:gelugor' => [
                ['name' => 'Glugor', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:pulau-pinang:timur-laut:bukit-bendera' => [
                ['name' => 'Penang Hill', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:pulau-pinang:seberang-perai-tengah:perai' => [
                ['name' => 'Prai', 'name_type' => 'alternative'],
            ],
            // Common names for Terengganu rows renamed to gazetted forms.
            'my:subdistrict:district:terengganu:kemaman:cukai' => [
                ['name' => 'Chukai', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:terengganu:kemaman:hulu-cukai' => [
                ['name' => 'Hulu Chukai', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:terengganu:kemaman:kemasik' => [
                ['name' => 'Kemasek', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:terengganu:kemaman:kertih' => [
                ['name' => 'Kerteh', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:terengganu:besut:jertih' => [
                ['name' => 'Jerteh', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:terengganu:marang:mercang' => [
                ['name' => 'Merchang', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:terengganu:besut:pengkalan-nangka' => [
                ['name' => 'Pangkalan Nangka', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:terengganu:setiu:caluk' => [
                ['name' => 'Chalok', 'name_type' => 'alternative'],
            ],
            // Common names for Perak rows renamed to gazetted forms.
            'my:subdistrict:district:perak:muallim:terolak' => [
                ['name' => 'Trolak', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:perak:kerian:simpang-empat' => [
                ['name' => 'Simpang Ampat Semanggol', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:perak:larut-matang:terung' => [
                ['name' => 'Terong', 'name_type' => 'alternative'],
                ['name' => 'Trong', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:perak:selama:hulu-ijok' => [
                ['name' => 'Ulu Ijok', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:perak:selama:hulu-selama' => [
                ['name' => 'Ulu Selama', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:perak:perak-tengah:pasir-panjang-hulu' => [
                ['name' => 'Pasir Panjang Ulu', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:perak:kuala-kangsar:sayung' => [
                ['name' => 'Saiong', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:perak:kinta:sungai-raya' => [
                ['name' => 'Sungai Raia', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:perak:muallim:hulu-bernam-barat' => [
                ['name' => 'Ulu Bernam Barat', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:perak:kinta:hulu-kinta' => [
                ['name' => 'Ulu Kinta', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:perak:perak-tengah:kampung-gajah' => [
                ['name' => 'Kampong Gajah', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:perak:kuala-kangsar:kampung-buaya' => [
                ['name' => 'Kampong Buaya', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:perak:manjung:beruas' => [
                ['name' => 'Bruas', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:perak:bagan-datuk:bagan-datuk' => [
                ['name' => 'Bagan Datoh', 'name_type' => 'alternative'],
            ],
            // Common names for Kedah rows renamed to gazetted forms.
            'my:subdistrict:district:kedah:kota-setar:gunung' => [
                ['name' => 'Gunong', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:kedah:kubang-pasu:changlun' => [
                ['name' => 'Changloon', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:kedah:kubang-pasu:hosba' => [
                ['name' => 'Husba', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:kedah:padang-terap:kurung-hitam' => [
                ['name' => 'Kurong Hitam', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:kedah:yan:guar-cempedak' => [
                ['name' => 'Guar Chempedak', 'name_type' => 'alternative'],
            ],
            // Common names for Kelantan rows renamed to gazetted forms.
            'my:subdistrict:district:kelantan:kota-bharu:baru-kubang-kerian' => [
                ['name' => 'Kubang Kerian', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:kelantan:lojing:kuala-betis' => [
                ['name' => 'Betis', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:kelantan:pasir-puteh:kampung-wakaf' => [
                ['name' => 'Kampong Wakaf', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:kelantan:tumpat:kampung-laut' => [
                ['name' => 'Kampong Laut', 'name_type' => 'alternative'],
            ],
            // Common names for Negeri Sembilan rows renamed to gazetted forms.
            'my:subdistrict:district:negeri-sembilan:seremban:baru-enstek' => [
                ['name' => 'Bandar Enstek', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:negeri-sembilan:jempol:serting-hulu' => [
                ['name' => 'Serting Ulu', 'name_type' => 'alternative'],
            ],
            'my:subdistrict:district:negeri-sembilan:rembau:sepri' => [
                ['name' => 'Spri', 'name_type' => 'alternative'],
            ],
        ];
    }

    /** @return array<string, list<array{parent_source_id: string, relationship_type: string, hierarchy_type: string}>> */
    public function areaRelationships(AddressCountry $country): array
    {
        $relationships = [];
        $areas = $this->addressAreaSource()->areas()->all();
        $parentSourceIds = [];

        foreach ($areas as $area) {
            $parentSourceIds[$area->sourceId] = $area->parentSourceId;
        }

        foreach ($areas as $area) {
            if ($area->parentSourceId === null) {
                continue;
            }

            $isPostal = in_array($area->type, ['locality', 'precinct'], true);
            $hierarchyType = $isPostal ? 'postal' : 'administrative';

            $relationships[$area->sourceId][] = [
                'parent_source_id' => $area->parentSourceId,
                'relationship_type' => 'contains',
                'hierarchy_type' => $hierarchyType,
            ];

            $stateSourceId = $this->stateAncestorSourceId($area->sourceId, $parentSourceIds);

            if ($stateSourceId !== null && $stateSourceId !== $area->parentSourceId) {
                $relationships[$area->sourceId][] = [
                    'parent_source_id' => $stateSourceId,
                    'relationship_type' => 'contains',
                    'hierarchy_type' => $hierarchyType,
                ];
            }
        }

        return $relationships;
    }

    /**
     * @param  array<string, string|null>  $parentSourceIds
     */
    private function stateAncestorSourceId(string $sourceId, array $parentSourceIds): ?string
    {
        $visited = [];
        $currentSourceId = $sourceId;

        while (isset($parentSourceIds[$currentSourceId])) {
            $parentSourceId = $parentSourceIds[$currentSourceId];

            if (isset($visited[$parentSourceId])) {
                return null;
            }

            if (str_starts_with($parentSourceId, 'my:state:')) {
                return $parentSourceId;
            }

            $visited[$parentSourceId] = true;
            $currentSourceId = $parentSourceId;
        }

        return null;
    }

    public function addressAreaSource(): AddressAreaSource
    {
        return new CsvAddressAreaSource(
            __DIR__ . '/../../../resources/geography/malaysia-address-areas.csv',
            self::AREA_SOURCE,
        );
    }

    /**
     * @return array<string, array{area_code: string, source: string, area_level: int, hierarchy_types: list<string>}>
     */
    public function stateAreaMappings(): array
    {
        /** @var array<string, string> */
        $areaCodes = [
            '01' => 'johor',
            '02' => 'kedah',
            '03' => 'kelantan',
            '04' => 'melaka',
            '05' => 'negeri-sembilan',
            '06' => 'pahang',
            '07' => 'pulau-pinang',
            '08' => 'perak',
            '09' => 'perlis',
            '10' => 'selangor',
            '11' => 'terengganu',
            '12' => 'sabah',
            '13' => 'sarawak',
            '14' => 'wp-kuala-lumpur',
            '15' => 'wp-labuan',
            '16' => 'wp-putrajaya',
        ];

        return array_map(
            static fn (string $areaCode): array => [
                'area_code' => $areaCode,
                'source' => self::AREA_SOURCE,
                'area_level' => 1,
                'hierarchy_types' => ['postal', 'administrative'],
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
            ['name' => 'Johor', 'code' => '01'],
            ['name' => 'Kedah', 'code' => '02'],
            ['name' => 'Kelantan', 'code' => '03'],
            ['name' => 'Melaka', 'code' => '04'],
            ['name' => 'Negeri Sembilan', 'code' => '05'],
            ['name' => 'Pahang', 'code' => '06'],
            ['name' => 'Perak', 'code' => '08'],
            ['name' => 'Perlis', 'code' => '09'],
            ['name' => 'Pulau Pinang', 'code' => '07'],
            ['name' => 'Sabah', 'code' => '12'],
            ['name' => 'Sarawak', 'code' => '13'],
            ['name' => 'Selangor', 'code' => '10'],
            ['name' => 'Terengganu', 'code' => '11'],
            ['name' => 'WP Kuala Lumpur', 'code' => '14'],
            ['name' => 'WP Labuan', 'code' => '15'],
            ['name' => 'WP Putrajaya', 'code' => '16'],
        ];
    }
}
