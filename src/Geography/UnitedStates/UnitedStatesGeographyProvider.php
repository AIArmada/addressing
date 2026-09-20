<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\UnitedStates;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class UnitedStatesGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_united_states_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.united_states';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'US';
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
                        key: 'state',
                        label: 'State / District / Territory',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['state', 'district', 'territory'],
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
                'district' => ['state'],
                'territory' => ['state'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'US', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/united-states-address-areas.csv',
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
            'AK' => 'AK',
            'AL' => 'AL',
            'AR' => 'AR',
            'AS' => 'AS',
            'AZ' => 'AZ',
            'CA' => 'CA',
            'CO' => 'CO',
            'CT' => 'CT',
            'DC' => 'DC',
            'DE' => 'DE',
            'FL' => 'FL',
            'GA' => 'GA',
            'GU' => 'GU',
            'HI' => 'HI',
            'IA' => 'IA',
            'ID' => 'ID',
            'IL' => 'IL',
            'IN' => 'IN',
            'KS' => 'KS',
            'KY' => 'KY',
            'LA' => 'LA',
            'MA' => 'MA',
            'MD' => 'MD',
            'ME' => 'ME',
            'MI' => 'MI',
            'MN' => 'MN',
            'MO' => 'MO',
            'MP' => 'MP',
            'MS' => 'MS',
            'MT' => 'MT',
            'NC' => 'NC',
            'ND' => 'ND',
            'NE' => 'NE',
            'NH' => 'NH',
            'NJ' => 'NJ',
            'NM' => 'NM',
            'NV' => 'NV',
            'NY' => 'NY',
            'OH' => 'OH',
            'OK' => 'OK',
            'OR' => 'OR',
            'PA' => 'PA',
            'PR' => 'PR',
            'RI' => 'RI',
            'SC' => 'SC',
            'SD' => 'SD',
            'TN' => 'TN',
            'TX' => 'TX',
            'UT' => 'UT',
            'VA' => 'VA',
            'VI' => 'VI',
            'VT' => 'VT',
            'WA' => 'WA',
            'WI' => 'WI',
            'WV' => 'WV',
            'WY' => 'WY',
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
            ['name' => 'Alaska', 'code' => 'AK'],
            ['name' => 'Alabama', 'code' => 'AL'],
            ['name' => 'Arkansas', 'code' => 'AR'],
            ['name' => 'American Samoa', 'code' => 'AS'],
            ['name' => 'Arizona', 'code' => 'AZ'],
            ['name' => 'California', 'code' => 'CA'],
            ['name' => 'Colorado', 'code' => 'CO'],
            ['name' => 'Connecticut', 'code' => 'CT'],
            ['name' => 'District of Columbia', 'code' => 'DC'],
            ['name' => 'Delaware', 'code' => 'DE'],
            ['name' => 'Florida', 'code' => 'FL'],
            ['name' => 'Georgia', 'code' => 'GA'],
            ['name' => 'Guam', 'code' => 'GU'],
            ['name' => 'Hawaii', 'code' => 'HI'],
            ['name' => 'Iowa', 'code' => 'IA'],
            ['name' => 'Idaho', 'code' => 'ID'],
            ['name' => 'Illinois', 'code' => 'IL'],
            ['name' => 'Indiana', 'code' => 'IN'],
            ['name' => 'Kansas', 'code' => 'KS'],
            ['name' => 'Kentucky', 'code' => 'KY'],
            ['name' => 'Louisiana', 'code' => 'LA'],
            ['name' => 'Massachusetts', 'code' => 'MA'],
            ['name' => 'Maryland', 'code' => 'MD'],
            ['name' => 'Maine', 'code' => 'ME'],
            ['name' => 'Michigan', 'code' => 'MI'],
            ['name' => 'Minnesota', 'code' => 'MN'],
            ['name' => 'Missouri', 'code' => 'MO'],
            ['name' => 'Northern Mariana Islands', 'code' => 'MP'],
            ['name' => 'Mississippi', 'code' => 'MS'],
            ['name' => 'Montana', 'code' => 'MT'],
            ['name' => 'North Carolina', 'code' => 'NC'],
            ['name' => 'North Dakota', 'code' => 'ND'],
            ['name' => 'Nebraska', 'code' => 'NE'],
            ['name' => 'New Hampshire', 'code' => 'NH'],
            ['name' => 'New Jersey', 'code' => 'NJ'],
            ['name' => 'New Mexico', 'code' => 'NM'],
            ['name' => 'Nevada', 'code' => 'NV'],
            ['name' => 'New York', 'code' => 'NY'],
            ['name' => 'Ohio', 'code' => 'OH'],
            ['name' => 'Oklahoma', 'code' => 'OK'],
            ['name' => 'Oregon', 'code' => 'OR'],
            ['name' => 'Pennsylvania', 'code' => 'PA'],
            ['name' => 'Puerto Rico', 'code' => 'PR'],
            ['name' => 'Rhode Island', 'code' => 'RI'],
            ['name' => 'South Carolina', 'code' => 'SC'],
            ['name' => 'South Dakota', 'code' => 'SD'],
            ['name' => 'Tennessee', 'code' => 'TN'],
            ['name' => 'Texas', 'code' => 'TX'],
            ['name' => 'Utah', 'code' => 'UT'],
            ['name' => 'Virginia', 'code' => 'VA'],
            ['name' => 'United States Virgin Islands', 'code' => 'VI'],
            ['name' => 'Vermont', 'code' => 'VT'],
            ['name' => 'Washington', 'code' => 'WA'],
            ['name' => 'Wisconsin', 'code' => 'WI'],
            ['name' => 'West Virginia', 'code' => 'WV'],
            ['name' => 'Wyoming', 'code' => 'WY'],
        ];
    }
}
