<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Canada;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Contracts\CountryPostalCodeNormalizer;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class CanadaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider, CountryPostalCodeNormalizer
{
    public const string AREA_SOURCE = 'aiarmada_addressing_canada_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.canada';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'CA';
    }

    /** @return list<string> */
    public function postalCodeLookupKeys(string $code): array
    {
        $code = mb_strtoupper((string) preg_replace('/\s+/', '', mb_trim($code)));
        $keys = [$code];

        // Full 6-char postcode (FSA + LDU): bundled codes are FSA level
        // (urban FSAs join municipalities, rural X0X join provinces);
        // the LDU carries no L2 signal.
        if (preg_match('/^([A-Z]\d[A-Z])\d[A-Z]\d$/', $code, $matches) === 1) {
            $keys[] = $matches[1];
        }

        return $keys;
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
                        label: 'Province / Territory',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['province', 'territory'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'municipality',
                        label: 'Census Subdivision',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['municipality', 'indigenous_reserve', 'unorganized'],
                        areaLevels: [2],
                        parentKey: 'province',
                        assignmentRole: 'municipality',
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
                'territory' => ['province'],
                'municipality' => ['municipality'],
                'indigenous_reserve' => ['municipality'],
                'unorganized' => ['municipality'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'CA', 'is_primary' => true],
                $areaRoles,
            );
        }

        return $roles;
    }

    /** @return array<string, list<array{name: string, name_type?: string, is_preferred?: bool}>> */
    public function areaNames(AddressCountry $country): array
    {
        // Canada Post abbreviations, mirroring the formatter map.
        return [
            'ca:province:alberta' => [
                ['name' => 'AB', 'name_type' => 'abbreviation'],
            ],
            'ca:province:british-columbia' => [
                ['name' => 'BC', 'name_type' => 'abbreviation'],
            ],
            'ca:province:manitoba' => [
                ['name' => 'MB', 'name_type' => 'abbreviation'],
            ],
            'ca:province:new-brunswick' => [
                ['name' => 'NB', 'name_type' => 'abbreviation'],
            ],
            'ca:province:newfoundland-and-labrador' => [
                ['name' => 'NL', 'name_type' => 'abbreviation'],
            ],
            'ca:province:nova-scotia' => [
                ['name' => 'NS', 'name_type' => 'abbreviation'],
            ],
            'ca:province:ontario' => [
                ['name' => 'ON', 'name_type' => 'abbreviation'],
            ],
            'ca:province:prince-edward-island' => [
                ['name' => 'PE', 'name_type' => 'abbreviation'],
            ],
            'ca:province:quebec' => [
                ['name' => 'QC', 'name_type' => 'abbreviation'],
                ['name' => 'Québec', 'name_type' => 'alternative'],
            ],
            'ca:province:saskatchewan' => [
                ['name' => 'SK', 'name_type' => 'abbreviation'],
            ],
            'ca:territory:northwest-territories' => [
                ['name' => 'NT', 'name_type' => 'abbreviation'],
            ],
            'ca:territory:nunavut' => [
                ['name' => 'NU', 'name_type' => 'abbreviation'],
            ],
            'ca:territory:yukon' => [
                ['name' => 'YT', 'name_type' => 'abbreviation'],
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
            __DIR__ . '/../../../resources/geography/canada-address-areas.csv',
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
            'AB' => 'AB',
            'BC' => 'BC',
            'MB' => 'MB',
            'NB' => 'NB',
            'NL' => 'NL',
            'NS' => 'NS',
            'NT' => 'NT',
            'NU' => 'NU',
            'ON' => 'ON',
            'PE' => 'PE',
            'QC' => 'QC',
            'SK' => 'SK',
            'YT' => 'YT',
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
            ['name' => 'Alberta', 'code' => 'AB'],
            ['name' => 'British Columbia', 'code' => 'BC'],
            ['name' => 'Manitoba', 'code' => 'MB'],
            ['name' => 'New Brunswick', 'code' => 'NB'],
            ['name' => 'Newfoundland and Labrador', 'code' => 'NL'],
            ['name' => 'Nova Scotia', 'code' => 'NS'],
            ['name' => 'Northwest Territories', 'code' => 'NT'],
            ['name' => 'Nunavut', 'code' => 'NU'],
            ['name' => 'Ontario', 'code' => 'ON'],
            ['name' => 'Prince Edward Island', 'code' => 'PE'],
            ['name' => 'Quebec', 'code' => 'QC'],
            ['name' => 'Saskatchewan', 'code' => 'SK'],
            ['name' => 'Yukon', 'code' => 'YT'],
        ];
    }
}
