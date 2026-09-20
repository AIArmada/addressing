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
