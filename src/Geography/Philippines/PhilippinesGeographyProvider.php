<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Philippines;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class PhilippinesGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_philippines_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.philippines';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'PH';
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
                        label: 'Province / National Capital Region',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['province', 'region'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'municipality',
                        label: 'City / Municipality / Sub-municipality',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['city', 'municipality', 'sub_municipality'],
                        areaLevels: [2],
                        parentKey: 'province',
                        assignmentRole: 'municipality',
                    ),
                    new AddressLevelDefinition(
                        key: 'barangay',
                        label: 'Barangay',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['barangay'],
                        areaLevels: [3],
                        parentKey: 'municipality',
                        assignmentRole: 'barangay',
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
                'region' => ['province'],
                'city' => ['municipality'],
                'municipality' => ['municipality'],
                'sub_municipality' => ['municipality'],
                'barangay' => ['barangay'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'PH', 'is_primary' => true],
                $areaRoles,
            );
        }

        return $roles;
    }

    /** @return array<string, list<array{name: string, name_type?: string, is_preferred?: bool}>> */
    public function areaNames(AddressCountry $country): array
    {
        return [
            'ph:province:samar' => [
                ['name' => 'Western Samar', 'name_type' => 'alternative'],
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
            __DIR__ . '/../../../resources/geography/philippines-address-areas.csv',
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
            'ABR' => 'ABR',
            'AGN' => 'AGN',
            'AGS' => 'AGS',
            'AKL' => 'AKL',
            'ALB' => 'ALB',
            'ANT' => 'ANT',
            'APA' => 'APA',
            'AUR' => 'AUR',
            'BAN' => 'BAN',
            'BAS' => 'BAS',
            'BEN' => 'BEN',
            'BIL' => 'BIL',
            'BOH' => 'BOH',
            'BTG' => 'BTG',
            'BTN' => 'BTN',
            'BUK' => 'BUK',
            'BUL' => 'BUL',
            'CAG' => 'CAG',
            'CAM' => 'CAM',
            'CAN' => 'CAN',
            'CAP' => 'CAP',
            'CAS' => 'CAS',
            'CAT' => 'CAT',
            'CAV' => 'CAV',
            'CEB' => 'CEB',
            'COM' => 'COM',
            'DAO' => 'DAO',
            'DAS' => 'DAS',
            'DAV' => 'DAV',
            'DIN' => 'DIN',
            'DVO' => 'DVO',
            'EAS' => 'EAS',
            'GUI' => 'GUI',
            'IFU' => 'IFU',
            'ILI' => 'ILI',
            'ILN' => 'ILN',
            'ILS' => 'ILS',
            'ISA' => 'ISA',
            'KAL' => 'KAL',
            'LAG' => 'LAG',
            'LAN' => 'LAN',
            'LAS' => 'LAS',
            'LEY' => 'LEY',
            'LUN' => 'LUN',
            'MAD' => 'MAD',
            'MAS' => 'MAS',
            'MDC' => 'MDC',
            'MDR' => 'MDR',
            'MGN' => 'MGN',
            'MGS' => 'MGS',
            'MOU' => 'MOU',
            'MSC' => 'MSC',
            'MSR' => 'MSR',
            'NCO' => 'NCO',
            'NEC' => 'NEC',
            'NER' => 'NER',
            'NSA' => 'NSA',
            'NUE' => 'NUE',
            'NUV' => 'NUV',
            'PAM' => 'PAM',
            'PAN' => 'PAN',
            'PLW' => 'PLW',
            'QUE' => 'QUE',
            'QUI' => 'QUI',
            'RIZ' => 'RIZ',
            'ROM' => 'ROM',
            'SAR' => 'SAR',
            'SCO' => 'SCO',
            'SIG' => 'SIG',
            'SLE' => 'SLE',
            'SLU' => 'SLU',
            'SOR' => 'SOR',
            'SUK' => 'SUK',
            'SUN' => 'SUN',
            'SUR' => 'SUR',
            'TAR' => 'TAR',
            'TAW' => 'TAW',
            'WSA' => 'WSA',
            'ZAN' => 'ZAN',
            'ZAS' => 'ZAS',
            'ZMB' => 'ZMB',
            'ZSI' => 'ZSI',
            '00' => '00',
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
        // Provinces come from the CSV; the 17 regions stay global states
        // only (name corrections, no areas).
        $regions = [
            ['name' => 'National Capital Region (Metro Manila)', 'code' => '00'],
            ['name' => 'Ilocos', 'code' => '01'],
            ['name' => 'Cagayan Valley', 'code' => '02'],
            ['name' => 'Central Luzon', 'code' => '03'],
            ['name' => 'Bicol', 'code' => '05'],
            ['name' => 'Western Visayas', 'code' => '06'],
            ['name' => 'Central Visayas', 'code' => '07'],
            ['name' => 'Eastern Visayas', 'code' => '08'],
            ['name' => 'Zamboanga Peninsula', 'code' => '09'],
            ['name' => 'Northern Mindanao', 'code' => '10'],
            ['name' => 'Davao', 'code' => '11'],
            ['name' => 'Soccsksargen', 'code' => '12'],
            ['name' => 'Caraga', 'code' => '13'],
            ['name' => 'Bangsamoro', 'code' => '14'],
            ['name' => 'Cordillera Administrative Region', 'code' => '15'],
            ['name' => 'Calabarzon', 'code' => '40'],
            ['name' => 'Mimaropa', 'code' => '41'],
        ];

        return array_merge($regions, [
            ['name' => 'Abra', 'code' => 'ABR'],
            ['name' => 'Agusan del Norte', 'code' => 'AGN'],
            ['name' => 'Agusan del Sur', 'code' => 'AGS'],
            ['name' => 'Aklan', 'code' => 'AKL'],
            ['name' => 'Albay', 'code' => 'ALB'],
            ['name' => 'Antique', 'code' => 'ANT'],
            ['name' => 'Apayao', 'code' => 'APA'],
            ['name' => 'Aurora', 'code' => 'AUR'],
            ['name' => 'Bataan', 'code' => 'BAN'],
            ['name' => 'Basilan', 'code' => 'BAS'],
            ['name' => 'Benguet', 'code' => 'BEN'],
            ['name' => 'Biliran', 'code' => 'BIL'],
            ['name' => 'Bohol', 'code' => 'BOH'],
            ['name' => 'Batangas', 'code' => 'BTG'],
            ['name' => 'Batanes', 'code' => 'BTN'],
            ['name' => 'Bukidnon', 'code' => 'BUK'],
            ['name' => 'Bulacan', 'code' => 'BUL'],
            ['name' => 'Cagayan', 'code' => 'CAG'],
            ['name' => 'Camiguin', 'code' => 'CAM'],
            ['name' => 'Camarines Norte', 'code' => 'CAN'],
            ['name' => 'Capiz', 'code' => 'CAP'],
            ['name' => 'Camarines Sur', 'code' => 'CAS'],
            ['name' => 'Catanduanes', 'code' => 'CAT'],
            ['name' => 'Cavite', 'code' => 'CAV'],
            ['name' => 'Cebu', 'code' => 'CEB'],
            ['name' => 'Davao de Oro', 'code' => 'COM'],
            ['name' => 'Davao Oriental', 'code' => 'DAO'],
            ['name' => 'Davao del Sur', 'code' => 'DAS'],
            ['name' => 'Davao del Norte', 'code' => 'DAV'],
            ['name' => 'Dinagat Islands', 'code' => 'DIN'],
            ['name' => 'Davao Occidental', 'code' => 'DVO'],
            ['name' => 'Eastern Samar', 'code' => 'EAS'],
            ['name' => 'Guimaras', 'code' => 'GUI'],
            ['name' => 'Ifugao', 'code' => 'IFU'],
            ['name' => 'Iloilo', 'code' => 'ILI'],
            ['name' => 'Ilocos Norte', 'code' => 'ILN'],
            ['name' => 'Ilocos Sur', 'code' => 'ILS'],
            ['name' => 'Isabela', 'code' => 'ISA'],
            ['name' => 'Kalinga', 'code' => 'KAL'],
            ['name' => 'Laguna', 'code' => 'LAG'],
            ['name' => 'Lanao del Norte', 'code' => 'LAN'],
            ['name' => 'Lanao del Sur', 'code' => 'LAS'],
            ['name' => 'Leyte', 'code' => 'LEY'],
            ['name' => 'La Union', 'code' => 'LUN'],
            ['name' => 'Marinduque', 'code' => 'MAD'],
            ['name' => 'Masbate', 'code' => 'MAS'],
            ['name' => 'Occidental Mindoro', 'code' => 'MDC'],
            ['name' => 'Oriental Mindoro', 'code' => 'MDR'],
            ['name' => 'Maguindanao del Norte', 'code' => 'MGN'],
            ['name' => 'Maguindanao del Sur', 'code' => 'MGS'],
            ['name' => 'Mountain Province', 'code' => 'MOU'],
            ['name' => 'Misamis Occidental', 'code' => 'MSC'],
            ['name' => 'Misamis Oriental', 'code' => 'MSR'],
            ['name' => 'Cotabato', 'code' => 'NCO'],
            ['name' => 'Negros Occidental', 'code' => 'NEC'],
            ['name' => 'Negros Oriental', 'code' => 'NER'],
            ['name' => 'Northern Samar', 'code' => 'NSA'],
            ['name' => 'Nueva Ecija', 'code' => 'NUE'],
            ['name' => 'Nueva Vizcaya', 'code' => 'NUV'],
            ['name' => 'Pampanga', 'code' => 'PAM'],
            ['name' => 'Pangasinan', 'code' => 'PAN'],
            ['name' => 'Palawan', 'code' => 'PLW'],
            ['name' => 'Quezon', 'code' => 'QUE'],
            ['name' => 'Quirino', 'code' => 'QUI'],
            ['name' => 'Rizal', 'code' => 'RIZ'],
            ['name' => 'Romblon', 'code' => 'ROM'],
            ['name' => 'Sarangani', 'code' => 'SAR'],
            ['name' => 'South Cotabato', 'code' => 'SCO'],
            ['name' => 'Siquijor', 'code' => 'SIG'],
            ['name' => 'Southern Leyte', 'code' => 'SLE'],
            ['name' => 'Sulu', 'code' => 'SLU'],
            ['name' => 'Sorsogon', 'code' => 'SOR'],
            ['name' => 'Sultan Kudarat', 'code' => 'SUK'],
            ['name' => 'Surigao del Norte', 'code' => 'SUN'],
            ['name' => 'Surigao del Sur', 'code' => 'SUR'],
            ['name' => 'Tarlac', 'code' => 'TAR'],
            ['name' => 'Tawi-Tawi', 'code' => 'TAW'],
            ['name' => 'Samar', 'code' => 'WSA'],
            ['name' => 'Zamboanga del Norte', 'code' => 'ZAN'],
            ['name' => 'Zamboanga del Sur', 'code' => 'ZAS'],
            ['name' => 'Zambales', 'code' => 'ZMB'],
            ['name' => 'Zamboanga Sibugay', 'code' => 'ZSI'],
        ]);
    }
}
