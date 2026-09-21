<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\India;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class IndiaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_india_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.india';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'IN';
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

        // ISO 3166-2:IN amendment of 23 November 2023 renamed subdivision
        // codes (CT->CG, OR->OD, TG->TS). Delete stragglers.
        $stateClass::query()
            ->where('country_id', $country->id)
            ->whereIn('code', ['CT', 'OR', 'TG'])
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
                        key: 'state',
                        label: 'State / Union Territory',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['state', 'union_territory'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'district',
                        label: 'District',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['district'],
                        areaLevels: [2],
                        parentKey: 'state',
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
                'state' => ['state'],
                'union_territory' => ['state'],
                'district' => ['district'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'IN', 'is_primary' => true],
                $areaRoles,
            );
        }

        return $roles;
    }

    /** @return array<string, list<array{name: string, name_type?: string, is_preferred?: bool}>> */
    public function areaNames(AddressCountry $country): array
    {
        return [
            'in:district:9' => [
                ['name' => 'Leh Ladakh', 'name_type' => 'alternative'],
            ],
            'in:district:21' => [
                ['name' => 'Lahaul And Spiti', 'name_type' => 'alternative'],
            ],
            'in:district:62' => [
                ['name' => 'Gurgaon', 'name_type' => 'historic'],
            ],
            'in:district:100' => [
                ['name' => 'Ganganagar', 'name_type' => 'alternative'],
            ],
            'in:district:120' => [
                ['name' => 'Allahabad', 'name_type' => 'historic'],
            ],
            'in:district:129' => [
                ['name' => 'Bara Banki', 'name_type' => 'alternative'],
            ],
            'in:district:140' => [
                ['name' => 'Faizabad', 'name_type' => 'historic'],
            ],
            'in:district:154' => [
                ['name' => 'Jyotiba Phule Nagar', 'name_type' => 'historic'],
                ['name' => 'J.P. Nagar', 'name_type' => 'abbreviation'],
            ],
            'in:district:163' => [
                ['name' => 'Mahamaya Nagar', 'name_type' => 'historic'],
            ],
            'in:district:164' => [
                ['name' => 'Mahrajganj', 'name_type' => 'alternative'],
            ],
            'in:district:179' => [
                ['name' => 'Sant Ravidas Nagar', 'name_type' => 'historic'],
            ],
            'in:district:225' => [
                ['name' => 'East Sikkim', 'name_type' => 'historic'],
            ],
            'in:district:226' => [
                ['name' => 'North Sikkim', 'name_type' => 'historic'],
            ],
            'in:district:227' => [
                ['name' => 'South Sikkim', 'name_type' => 'historic'],
            ],
            'in:district:228' => [
                ['name' => 'West Sikkim', 'name_type' => 'historic'],
            ],
            'in:district:293' => [
                ['name' => 'Karimganj', 'name_type' => 'historic'],
            ],
            'in:district:296' => [
                ['name' => 'Marigaon', 'name_type' => 'alternative'],
            ],
            'in:district:316' => [
                ['name' => 'Maldah', 'name_type' => 'alternative'],
            ],
            'in:district:327' => [
                ['name' => 'East Singhbum', 'name_type' => 'alternative'],
            ],
            'in:district:355' => [
                ['name' => 'Jagatsinghapur', 'name_type' => 'alternative'],
            ],
            'in:district:372' => [
                ['name' => 'Sonepur', 'name_type' => 'alternative'],
            ],
            'in:district:376' => [
                ['name' => 'Dantewada', 'name_type' => 'common'],
            ],
            'in:district:381' => [
                ['name' => 'Kanker', 'name_type' => 'common'],
            ],
            'in:district:382' => [
                ['name' => 'Kabeerdham', 'name_type' => 'alternative'],
            ],
            'in:district:405' => [
                ['name' => 'East Nimar', 'name_type' => 'historic'],
                ['name' => 'Khandwa (East Nimar)', 'name_type' => 'alternative'],
            ],
            'in:district:409' => [
                ['name' => 'Hoshangabad', 'name_type' => 'historic'],
            ],
            'in:district:414' => [
                ['name' => 'West Nimar', 'name_type' => 'historic'],
                ['name' => 'Khargone (West Nimar)', 'name_type' => 'alternative'],
            ],
            'in:district:418' => [
                ['name' => 'Narsimhapur', 'name_type' => 'alternative'],
            ],
            'in:district:441' => [
                ['name' => 'Banas Kantha', 'name_type' => 'alternative'],
            ],
            'in:district:444' => [
                ['name' => 'Dangs', 'name_type' => 'alternative'],
            ],
            'in:district:449' => [
                ['name' => 'Kutch', 'name_type' => 'common'],
            ],
            'in:district:451' => [
                ['name' => 'Mahesana', 'name_type' => 'alternative'],
            ],
            'in:district:454' => [
                ['name' => 'Panch Mahals', 'name_type' => 'alternative'],
            ],
            'in:district:458' => [
                ['name' => 'Sabar Kantha', 'name_type' => 'alternative'],
            ],
            'in:district:466' => [
                ['name' => 'Ahmednagar', 'name_type' => 'historic'],
            ],
            'in:district:469' => [
                ['name' => 'Aurangabad', 'name_type' => 'historic'],
            ],
            'in:district:482' => [
                ['name' => 'Mumbai', 'name_type' => 'alternative'],
            ],
            'in:district:488' => [
                ['name' => 'Osmanabad', 'name_type' => 'historic'],
            ],
            'in:district:504' => [
                ['name' => 'Y.S.R. Kadapa', 'name_type' => 'alternative'],
            ],
            'in:district:515' => [
                ['name' => 'Nellore', 'name_type' => 'common'],
            ],
            'in:district:553' => [
                ['name' => 'Lakshadweep District', 'name_type' => 'alternative'],
            ],
            'in:district:599' => [
                ['name' => 'Mahé', 'name_type' => 'alternative'],
            ],
            'in:district:600' => [
                ['name' => 'Pondicherry', 'name_type' => 'historic'],
            ],
            'in:district:602' => [
                ['name' => 'South Andamans', 'name_type' => 'alternative'],
            ],
            'in:district:603' => [
                ['name' => 'Nicobars', 'name_type' => 'alternative'],
            ],
            'in:district:604' => [
                ['name' => 'Mewat', 'name_type' => 'historic'],
            ],
            'in:district:608' => [
                ['name' => 'Sahibzada Ajit Singh Nagar', 'name_type' => 'official'],
            ],
            'in:district:618' => [
                ['name' => 'Kamrup Metro', 'name_type' => 'alternative'],
            ],
            'in:district:631' => [
                ['name' => 'Ramanagara', 'name_type' => 'historic'],
            ],
            'in:district:632' => [
                ['name' => 'North And Middle Andaman', 'name_type' => 'alternative'],
            ],
            'in:district:633' => [
                ['name' => 'Kanshiram Nagar', 'name_type' => 'historic'],
            ],
            'in:district:640' => [
                ['name' => 'Chhatrapati Shahuji Maharaj Nagar', 'name_type' => 'historic'],
                ['name' => 'CSM Nagar', 'name_type' => 'abbreviation'],
            ],
            'in:district:645' => [
                ['name' => 'Gariyaband', 'name_type' => 'alternative'],
            ],
            'in:district:659' => [
                ['name' => 'Bheem Nagar', 'name_type' => 'historic'],
            ],
            'in:district:660' => [
                ['name' => 'Prabuddha Nagar', 'name_type' => 'historic'],
            ],
            'in:district:661' => [
                ['name' => 'Panchsheel Nagar', 'name_type' => 'historic'],
            ],
            'in:district:686' => [
                ['name' => 'Warangal Urban', 'name_type' => 'historic'],
            ],
            'in:district:687' => [
                ['name' => 'Jayashankar Bhupalapally', 'name_type' => 'alternative'],
            ],
            'in:district:689' => [
                ['name' => 'Jangoan', 'name_type' => 'alternative'],
            ],
            'in:district:707' => [
                ['name' => 'South Salmara Mancachar', 'name_type' => 'alternative'],
            ],
            'in:district:723' => [
                ['name' => 'Pakke Kessang', 'name_type' => 'alternative'],
            ],
            'in:district:724' => [
                ['name' => 'Leparada', 'name_type' => 'alternative'],
            ],
            'in:district:725' => [
                ['name' => 'Shi Yomi', 'name_type' => 'alternative'],
            ],
            'in:district:747' => [
                ['name' => 'Konaseema', 'name_type' => 'common'],
            ],
            'in:district:749' => [
                ['name' => 'NTR District', 'name_type' => 'common'],
            ],
            'in:district:757' => [
                ['name' => 'Tseminyü', 'name_type' => 'alternative'],
            ],
            'in:district:758' => [
                ['name' => 'Chumoukedima', 'name_type' => 'alternative'],
                ['name' => 'Chumukedima', 'name_type' => 'common'],
            ],
            'in:district:761' => [
                ['name' => 'Mohla-Manpur-Ambagarh Chouki', 'name_type' => 'alternative'],
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
            __DIR__ . '/../../../resources/geography/india-address-areas.csv',
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
            'AN' => 'AN',
            'AP' => 'AP',
            'AR' => 'AR',
            'AS' => 'AS',
            'BR' => 'BR',
            'CH' => 'CH',
            'CG' => 'CG',
            'DH' => 'DH',
            'DL' => 'DL',
            'GA' => 'GA',
            'GJ' => 'GJ',
            'HP' => 'HP',
            'HR' => 'HR',
            'JH' => 'JH',
            'JK' => 'JK',
            'KA' => 'KA',
            'KL' => 'KL',
            'LA' => 'LA',
            'LD' => 'LD',
            'MH' => 'MH',
            'ML' => 'ML',
            'MN' => 'MN',
            'MP' => 'MP',
            'MZ' => 'MZ',
            'NL' => 'NL',
            'OD' => 'OD',
            'PB' => 'PB',
            'PY' => 'PY',
            'RJ' => 'RJ',
            'SK' => 'SK',
            'TS' => 'TS',
            'TN' => 'TN',
            'TR' => 'TR',
            'UK' => 'UK',
            'UP' => 'UP',
            'WB' => 'WB',
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
            ['name' => 'Andaman and Nicobar Islands', 'code' => 'AN'],
            ['name' => 'Andhra Pradesh', 'code' => 'AP'],
            ['name' => 'Arunachal Pradesh', 'code' => 'AR'],
            ['name' => 'Assam', 'code' => 'AS'],
            ['name' => 'Bihar', 'code' => 'BR'],
            ['name' => 'Chandigarh', 'code' => 'CH'],
            ['name' => 'Chhattisgarh', 'code' => 'CG'],
            ['name' => 'Dadra and Nagar Haveli and Daman and Diu', 'code' => 'DH'],
            ['name' => 'Delhi', 'code' => 'DL'],
            ['name' => 'Goa', 'code' => 'GA'],
            ['name' => 'Gujarat', 'code' => 'GJ'],
            ['name' => 'Himachal Pradesh', 'code' => 'HP'],
            ['name' => 'Haryana', 'code' => 'HR'],
            ['name' => 'Jharkhand', 'code' => 'JH'],
            ['name' => 'Jammu and Kashmir', 'code' => 'JK'],
            ['name' => 'Karnataka', 'code' => 'KA'],
            ['name' => 'Kerala', 'code' => 'KL'],
            ['name' => 'Ladakh', 'code' => 'LA'],
            ['name' => 'Lakshadweep', 'code' => 'LD'],
            ['name' => 'Maharashtra', 'code' => 'MH'],
            ['name' => 'Meghalaya', 'code' => 'ML'],
            ['name' => 'Manipur', 'code' => 'MN'],
            ['name' => 'Madhya Pradesh', 'code' => 'MP'],
            ['name' => 'Mizoram', 'code' => 'MZ'],
            ['name' => 'Nagaland', 'code' => 'NL'],
            ['name' => 'Odisha', 'code' => 'OD'],
            ['name' => 'Punjab', 'code' => 'PB'],
            ['name' => 'Puducherry', 'code' => 'PY'],
            ['name' => 'Rajasthan', 'code' => 'RJ'],
            ['name' => 'Sikkim', 'code' => 'SK'],
            ['name' => 'Telangana', 'code' => 'TS'],
            ['name' => 'Tamil Nadu', 'code' => 'TN'],
            ['name' => 'Tripura', 'code' => 'TR'],
            ['name' => 'Uttarakhand', 'code' => 'UK'],
            ['name' => 'Uttar Pradesh', 'code' => 'UP'],
            ['name' => 'West Bengal', 'code' => 'WB'],
        ];
    }
}
