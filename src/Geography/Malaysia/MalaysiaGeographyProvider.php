<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Malaysia;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class MalaysiaGeographyProvider implements CountryGeographyProvider, CountryHierarchyProvider
{
    private const string AREA_SOURCE = 'aiarmada_addressing_malaysia_v1';

    public function countryCode(): string
    {
        return 'MY';
    }

    public function seed(AddressCountry $malaysia): void
    {
        $stateClass = ModelResolver::stateClass();
        $statesData = $this->stateDefinitions();

        foreach ($statesData as $s) {
            $stateClass::firstOrCreate(
                ['country_id' => $malaysia->id, 'code' => $s['code']],
                [
                    'name' => $s['name'],
                    'label' => $s['label'],
                    'metadata' => $s['metadata'] ?? null,
                ],
            );
        }
    }

    /**
     * @return list<AddressLevelDefinition>
     */
    public function addressLevels(): array
    {
        return [
            new AddressLevelDefinition(
                key: 'state',
                label: 'State / Federal Territory',
                storageColumn: 'state_id',
                kind: 'state',
                areaTypes: ['state', 'wilayah_persekutuan'],
                areaLevel: 1,
            ),
            new AddressLevelDefinition(
                key: 'district',
                label: 'District / Precinct',
                storageColumn: 'admin_area_1_id',
                kind: 'area',
                areaTypes: ['district', 'precinct', 'locality'],
                areaLevel: 2,
                parentKey: 'state',
            ),
            new AddressLevelDefinition(
                key: 'mukim',
                label: 'Mukim / Subdistrict',
                storageColumn: 'admin_area_2_id',
                kind: 'area',
                areaTypes: ['mukim', 'subdistrict'],
                areaLevel: 3,
                parentKey: 'district',
            ),
        ];
    }

    public function addressAreaSource(): AddressAreaSource
    {
        return new CsvAddressAreaSource(
            __DIR__ . '/../../../resources/geography/malaysia-address-areas.csv',
            self::AREA_SOURCE,
        );
    }

    /**
     * @return array<string, array{area_code: string, source: string, area_level: int}>
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
            ],
            $areaCodes,
        );
    }

    /**
     * @return list<array{name: string, code: string, label: string, metadata?: array}>
     */
    private function stateDefinitions(): array
    {
        return [
            ['name' => 'Johor', 'code' => '01', 'label' => 'Johor'],
            ['name' => 'Kedah', 'code' => '02', 'label' => 'Kedah'],
            ['name' => 'Kelantan', 'code' => '03', 'label' => 'Kelantan'],
            ['name' => 'Melaka', 'code' => '04', 'label' => 'Melaka'],
            ['name' => 'Negeri Sembilan', 'code' => '05', 'label' => 'Negeri Sembilan'],
            ['name' => 'Pahang', 'code' => '06', 'label' => 'Pahang'],
            ['name' => 'Perak', 'code' => '08', 'label' => 'Perak'],
            ['name' => 'Perlis', 'code' => '09', 'label' => 'Perlis'],
            ['name' => 'Pulau Pinang', 'code' => '07', 'label' => 'Pulau Pinang'],
            ['name' => 'Sabah', 'code' => '12', 'label' => 'Sabah'],
            ['name' => 'Sarawak', 'code' => '13', 'label' => 'Sarawak'],
            ['name' => 'Selangor', 'code' => '10', 'label' => 'Selangor'],
            ['name' => 'Terengganu', 'code' => '11', 'label' => 'Terengganu'],
            ['name' => 'WP Kuala Lumpur', 'code' => '14', 'label' => 'Wilayah Persekutuan Kuala Lumpur'],
            ['name' => 'WP Labuan', 'code' => '15', 'label' => 'Wilayah Persekutuan Labuan'],
            ['name' => 'WP Putrajaya', 'code' => '16', 'label' => 'Wilayah Persekutuan Putrajaya'],
        ];
    }
}
