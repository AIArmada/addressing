<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Indonesia;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryAreaTypeLabelProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CompositeAddressAreaSource;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class IndonesiaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_indonesia_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.indonesia';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'ID';
    }

    public function seed(AddressCountry $indonesia): void
    {
        $stateClass = ModelResolver::stateClass();
        $statesData = $this->stateDefinitions();

        foreach ($statesData as $s) {
            $stateClass::updateOrCreate(
                ['country_id' => $indonesia->id, 'code' => $s['code']],
                [
                    'name' => $s['name'],
                    'country_code' => $this->countryCode(),
                ],
            );
        }

        // ISO geographical units (island groups) are not provinces and were
        // removed from the bundled state data. Delete stragglers seeded
        // before that fix so names like Papua always resolve to a province.
        $stateClass::query()
            ->where('country_id', $indonesia->id)
            ->whereIn('code', ['JW', 'KA', 'ML', 'NU', 'PP', 'SL', 'SM'])
            ->delete();
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
                        key: 'regency',
                        label: 'Regency / City',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['regency', 'city'],
                        areaLevels: [2],
                        parentKey: 'province',
                        assignmentRole: 'regency',
                    ),
                    new AddressLevelDefinition(
                        key: 'district',
                        label: 'Kecamatan',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['district'],
                        areaLevels: [3],
                        parentKey: 'regency',
                        assignmentRole: 'district',
                    ),
                    new AddressLevelDefinition(
                        key: 'village',
                        label: 'Desa / Kelurahan',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['village', 'urban_village'],
                        areaLevels: [4],
                        parentKey: 'district',
                        assignmentRole: 'village',
                    ),
                ],
            ),
        ];
    }

    /** @return array<string, string> */
    public function areaTypeLabels(): array
    {
        // Kota is the proper term; the bare headline City would collide
        // with other countries' city translations downstream. Kecamatan,
        // Desa, and Kelurahan are the national administrative terms (the
        // formatter already addresses them by these names); states with
        // special-autonomy terms override below.
        return [
            'city' => 'Kota',
            'district' => 'Kecamatan',
            'village' => 'Desa',
            'urban_village' => 'Kelurahan',
        ];
    }

    /** @return list<array{state_code: string, type_labels: array<string, string>}> */
    public function stateAreaTypeLabels(): array
    {
        // State codes are the ISO-like State codes (AC, SB, ...), matching
        // what CountryAddressProfileResolver::stateCode() resolves.
        return [
            // Aceh: Law 11/2006 (UUPA) art. 115 — gampong in regencies
            // and cities alike (BPS Kota Banda Aceh Dalam Angka).
            ['state_code' => 'AC', 'type_labels' => ['village' => 'Gampong', 'urban_village' => 'Gampong']],
            // West Sumatra: nagari restored province-wide in 2001; only
            // villages inside municipalities stay kelurahan.
            ['state_code' => 'SB', 'type_labels' => ['village' => 'Nagari']],
            // Yogyakarta: Perda DIY — kapanewon in regencies, kemantren
            // in the city (combined: the contract keys state+type only),
            // kalurahan for rural villages; urban stays kelurahan.
            ['state_code' => 'YO', 'type_labels' => ['district' => 'Kapanewon / Kemantren', 'village' => 'Kalurahan']],
            // Papua region: Otsus Law 21/2001 — kecamatan becomes
            // distrik, desa becomes kampung.
            ['state_code' => 'PA', 'type_labels' => ['district' => 'Distrik', 'village' => 'Kampung']],
            ['state_code' => 'PB', 'type_labels' => ['district' => 'Distrik', 'village' => 'Kampung']],
            ['state_code' => 'PS', 'type_labels' => ['district' => 'Distrik', 'village' => 'Kampung']],
            ['state_code' => 'PT', 'type_labels' => ['district' => 'Distrik', 'village' => 'Kampung']],
            ['state_code' => 'PE', 'type_labels' => ['district' => 'Distrik', 'village' => 'Kampung']],
            ['state_code' => 'PD', 'type_labels' => ['district' => 'Distrik', 'village' => 'Kampung']],
        ];
    }

    /** @return array<string, list<array{role: string, country_code?: string, is_primary?: bool}>> */
    public function areaRoles(AddressCountry $country): array
    {
        $roles = [];

        foreach ($this->addressAreaSource()->areas() as $area) {
            $areaRoles = match ($area->type) {
                'province' => ['province'],
                'regency' => ['regency'],
                'city' => ['regency'],
                'district' => ['district'],
                'village' => ['village'],
                'urban_village' => ['village'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'ID', 'is_primary' => true],
                $areaRoles,
            );
        }

        return $roles;
    }

    /** @return array<string, list<array{name: string, name_type?: string, is_preferred?: bool}>> */
    public function areaNames(AddressCountry $country): array
    {
        return [
            'id:province:11' => [
                ['name' => 'Nanggroe Aceh Darussalam', 'name_type' => 'historic'],
            ],
            'id:province:31' => [
                ['name' => 'Jakarta', 'name_type' => 'common', 'is_preferred' => true],
                ['name' => 'Daerah Khusus Jakarta', 'name_type' => 'official'],
                ['name' => 'DKJ', 'name_type' => 'abbreviation'],
            ],
            'id:province:34' => [
                ['name' => 'Daerah Istimewa Yogyakarta', 'name_type' => 'official'],
            ],
            'id:province:91' => [
                ['name' => 'Irian Jaya', 'name_type' => 'historic'],
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

            $relationships[$area->sourceId][] = [
                'parent_source_id' => $area->parentSourceId,
                'relationship_type' => 'contains',
                'hierarchy_type' => 'administrative',
            ];

            $stateSourceId = $this->stateAncestorSourceId($area->sourceId, $parentSourceIds);

            if ($stateSourceId !== null && $stateSourceId !== $area->parentSourceId) {
                $relationships[$area->sourceId][] = [
                    'parent_source_id' => $stateSourceId,
                    'relationship_type' => 'contains',
                    'hierarchy_type' => 'administrative',
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

            if (str_starts_with($parentSourceId, 'id:province:')) {
                return $parentSourceId;
            }

            $visited[$parentSourceId] = true;
            $currentSourceId = $parentSourceId;
        }

        return null;
    }

    public function addressAreaSource(): AddressAreaSource
    {
        $main = new CsvAddressAreaSource(
            __DIR__ . '/../../../resources/geography/indonesia-address-areas.csv',
            self::AREA_SOURCE,
        );

        if (! config('addressing.geography.indonesia.villages', false)) {
            return $main;
        }

        // Villages share the main source key because area parents resolve
        // by (source, source_id) within a single import run.
        return new CompositeAddressAreaSource([
            $main,
            new CsvAddressAreaSource(
                __DIR__ . '/../../../resources/geography/indonesia-villages.csv',
                self::AREA_SOURCE,
            ),
        ]);
    }

    /**
     * ISO 3166-2 second part to Kemendagri province code.
     *
     * @return array<string, array{area_code: string, source: string, area_level: int, hierarchy_types?: list<string>}>
     */
    public function stateAreaMappings(): array
    {
        /** @var array<string, string> */
        $areaCodes = [
            'AC' => '11',
            'SU' => '12',
            'SB' => '13',
            'RI' => '14',
            'JA' => '15',
            'SS' => '16',
            'BE' => '17',
            'LA' => '18',
            'BB' => '19',
            'KR' => '21',
            'JK' => '31',
            'JB' => '32',
            'JT' => '33',
            'YO' => '34',
            'JI' => '35',
            'BT' => '36',
            'BA' => '51',
            'NB' => '52',
            'NT' => '53',
            'KB' => '61',
            'KT' => '62',
            'KS' => '63',
            'KI' => '64',
            'KU' => '65',
            'SA' => '71',
            'ST' => '72',
            'SN' => '73',
            'SG' => '74',
            'GO' => '75',
            'SR' => '76',
            'MA' => '81',
            'MU' => '82',
            'PA' => '91',
            'PB' => '92',
            'PS' => '93',
            'PT' => '94',
            'PE' => '95',
            'PD' => '96',
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
            ['name' => 'Aceh', 'code' => 'AC'],
            ['name' => 'Sumatera Utara', 'code' => 'SU'],
            ['name' => 'Sumatera Barat', 'code' => 'SB'],
            ['name' => 'Riau', 'code' => 'RI'],
            ['name' => 'Jambi', 'code' => 'JA'],
            ['name' => 'Sumatera Selatan', 'code' => 'SS'],
            ['name' => 'Bengkulu', 'code' => 'BE'],
            ['name' => 'Lampung', 'code' => 'LA'],
            ['name' => 'Kepulauan Bangka Belitung', 'code' => 'BB'],
            ['name' => 'Kepulauan Riau', 'code' => 'KR'],
            ['name' => 'DKI Jakarta', 'code' => 'JK'],
            ['name' => 'Jawa Barat', 'code' => 'JB'],
            ['name' => 'Jawa Tengah', 'code' => 'JT'],
            ['name' => 'DI Yogyakarta', 'code' => 'YO'],
            ['name' => 'Jawa Timur', 'code' => 'JI'],
            ['name' => 'Banten', 'code' => 'BT'],
            ['name' => 'Bali', 'code' => 'BA'],
            ['name' => 'Nusa Tenggara Barat', 'code' => 'NB'],
            ['name' => 'Nusa Tenggara Timur', 'code' => 'NT'],
            ['name' => 'Kalimantan Barat', 'code' => 'KB'],
            ['name' => 'Kalimantan Tengah', 'code' => 'KT'],
            ['name' => 'Kalimantan Selatan', 'code' => 'KS'],
            ['name' => 'Kalimantan Timur', 'code' => 'KI'],
            ['name' => 'Kalimantan Utara', 'code' => 'KU'],
            ['name' => 'Sulawesi Utara', 'code' => 'SA'],
            ['name' => 'Sulawesi Tengah', 'code' => 'ST'],
            ['name' => 'Sulawesi Selatan', 'code' => 'SN'],
            ['name' => 'Sulawesi Tenggara', 'code' => 'SG'],
            ['name' => 'Gorontalo', 'code' => 'GO'],
            ['name' => 'Sulawesi Barat', 'code' => 'SR'],
            ['name' => 'Maluku', 'code' => 'MA'],
            ['name' => 'Maluku Utara', 'code' => 'MU'],
            ['name' => 'Papua', 'code' => 'PA'],
            ['name' => 'Papua Barat', 'code' => 'PB'],
            ['name' => 'Papua Selatan', 'code' => 'PS'],
            ['name' => 'Papua Tengah', 'code' => 'PT'],
            ['name' => 'Papua Pegunungan', 'code' => 'PE'],
            ['name' => 'Papua Barat Daya', 'code' => 'PD'],
        ];
    }
}
