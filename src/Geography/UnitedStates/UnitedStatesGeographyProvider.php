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
                    new AddressLevelDefinition(
                        key: 'county',
                        label: 'County / County Equivalent',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['county', 'parish', 'borough', 'census_area', 'city', 'municipality', 'planning_region'],
                        areaLevels: [2],
                        parentKey: 'state',
                        assignmentRole: 'county',
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
                'county' => ['county'],
                'parish' => ['county'],
                'borough' => ['county'],
                'census_area' => ['county'],
                'city' => ['county'],
                'municipality' => ['county'],
                'planning_region' => ['county'],
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
        // USPS Publication 28 abbreviations, mirroring the formatter map
        // (military AA/AE/AP intentionally excluded: not areas).
        return [
            'us:district:district-of-columbia' => [
                ['name' => 'DC', 'name_type' => 'abbreviation'],
            ],
            'us:state:alabama' => [
                ['name' => 'AL', 'name_type' => 'abbreviation'],
            ],
            'us:state:alaska' => [
                ['name' => 'AK', 'name_type' => 'abbreviation'],
            ],
            'us:state:arizona' => [
                ['name' => 'AZ', 'name_type' => 'abbreviation'],
            ],
            'us:state:arkansas' => [
                ['name' => 'AR', 'name_type' => 'abbreviation'],
            ],
            'us:state:california' => [
                ['name' => 'CA', 'name_type' => 'abbreviation'],
            ],
            'us:state:colorado' => [
                ['name' => 'CO', 'name_type' => 'abbreviation'],
            ],
            'us:state:connecticut' => [
                ['name' => 'CT', 'name_type' => 'abbreviation'],
            ],
            'us:state:delaware' => [
                ['name' => 'DE', 'name_type' => 'abbreviation'],
            ],
            'us:state:florida' => [
                ['name' => 'FL', 'name_type' => 'abbreviation'],
            ],
            'us:state:georgia' => [
                ['name' => 'GA', 'name_type' => 'abbreviation'],
            ],
            'us:state:hawaii' => [
                ['name' => 'HI', 'name_type' => 'abbreviation'],
            ],
            'us:state:idaho' => [
                ['name' => 'ID', 'name_type' => 'abbreviation'],
            ],
            'us:state:illinois' => [
                ['name' => 'IL', 'name_type' => 'abbreviation'],
            ],
            'us:state:indiana' => [
                ['name' => 'IN', 'name_type' => 'abbreviation'],
            ],
            'us:state:iowa' => [
                ['name' => 'IA', 'name_type' => 'abbreviation'],
            ],
            'us:state:kansas' => [
                ['name' => 'KS', 'name_type' => 'abbreviation'],
            ],
            'us:state:kentucky' => [
                ['name' => 'KY', 'name_type' => 'abbreviation'],
            ],
            'us:state:louisiana' => [
                ['name' => 'LA', 'name_type' => 'abbreviation'],
            ],
            'us:state:maine' => [
                ['name' => 'ME', 'name_type' => 'abbreviation'],
            ],
            'us:state:maryland' => [
                ['name' => 'MD', 'name_type' => 'abbreviation'],
            ],
            'us:state:massachusetts' => [
                ['name' => 'MA', 'name_type' => 'abbreviation'],
            ],
            'us:state:michigan' => [
                ['name' => 'MI', 'name_type' => 'abbreviation'],
            ],
            'us:state:minnesota' => [
                ['name' => 'MN', 'name_type' => 'abbreviation'],
            ],
            'us:state:mississippi' => [
                ['name' => 'MS', 'name_type' => 'abbreviation'],
            ],
            'us:state:missouri' => [
                ['name' => 'MO', 'name_type' => 'abbreviation'],
            ],
            'us:state:montana' => [
                ['name' => 'MT', 'name_type' => 'abbreviation'],
            ],
            'us:state:nebraska' => [
                ['name' => 'NE', 'name_type' => 'abbreviation'],
            ],
            'us:state:nevada' => [
                ['name' => 'NV', 'name_type' => 'abbreviation'],
            ],
            'us:state:new-hampshire' => [
                ['name' => 'NH', 'name_type' => 'abbreviation'],
            ],
            'us:state:new-jersey' => [
                ['name' => 'NJ', 'name_type' => 'abbreviation'],
            ],
            'us:state:new-mexico' => [
                ['name' => 'NM', 'name_type' => 'abbreviation'],
            ],
            'us:state:new-york' => [
                ['name' => 'NY', 'name_type' => 'abbreviation'],
            ],
            'us:state:north-carolina' => [
                ['name' => 'NC', 'name_type' => 'abbreviation'],
            ],
            'us:state:north-dakota' => [
                ['name' => 'ND', 'name_type' => 'abbreviation'],
            ],
            'us:state:ohio' => [
                ['name' => 'OH', 'name_type' => 'abbreviation'],
            ],
            'us:state:oklahoma' => [
                ['name' => 'OK', 'name_type' => 'abbreviation'],
            ],
            'us:state:oregon' => [
                ['name' => 'OR', 'name_type' => 'abbreviation'],
            ],
            'us:state:pennsylvania' => [
                ['name' => 'PA', 'name_type' => 'abbreviation'],
            ],
            'us:state:rhode-island' => [
                ['name' => 'RI', 'name_type' => 'abbreviation'],
            ],
            'us:state:south-carolina' => [
                ['name' => 'SC', 'name_type' => 'abbreviation'],
            ],
            'us:state:south-dakota' => [
                ['name' => 'SD', 'name_type' => 'abbreviation'],
            ],
            'us:state:tennessee' => [
                ['name' => 'TN', 'name_type' => 'abbreviation'],
            ],
            'us:state:texas' => [
                ['name' => 'TX', 'name_type' => 'abbreviation'],
            ],
            'us:state:utah' => [
                ['name' => 'UT', 'name_type' => 'abbreviation'],
            ],
            'us:state:vermont' => [
                ['name' => 'VT', 'name_type' => 'abbreviation'],
            ],
            'us:state:virginia' => [
                ['name' => 'VA', 'name_type' => 'abbreviation'],
            ],
            'us:state:washington' => [
                ['name' => 'WA', 'name_type' => 'abbreviation'],
            ],
            'us:state:west-virginia' => [
                ['name' => 'WV', 'name_type' => 'abbreviation'],
            ],
            'us:state:wisconsin' => [
                ['name' => 'WI', 'name_type' => 'abbreviation'],
            ],
            'us:state:wyoming' => [
                ['name' => 'WY', 'name_type' => 'abbreviation'],
            ],
            'us:territory:american-samoa' => [
                ['name' => 'AS', 'name_type' => 'abbreviation'],
            ],
            'us:territory:guam' => [
                ['name' => 'GU', 'name_type' => 'abbreviation'],
            ],
            'us:territory:northern-mariana-islands' => [
                ['name' => 'MP', 'name_type' => 'abbreviation'],
            ],
            'us:territory:puerto-rico' => [
                ['name' => 'PR', 'name_type' => 'abbreviation'],
            ],
            'us:territory:united-states-virgin-islands' => [
                ['name' => 'VI', 'name_type' => 'abbreviation'],
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
