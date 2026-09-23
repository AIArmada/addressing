<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Germany;

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

class GermanyGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_germany_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.germany';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'DE';
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
                        label: 'State (Land)',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['state'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'district',
                        label: 'District (Landkreis / Kreisfreie Stadt)',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['rural_district', 'urban_district'],
                        areaLevels: [2],
                        parentKey: 'state',
                        assignmentRole: 'district',
                    ),
                ],
            ),
        ];
    }

    /** @return array<string, string> */
    public function areaTypeLabels(): array
    {
        // State rows use German official names, so the type labels use
        // the German administrative terms: Land for the 16 Länder,
        // Landkreis and Kreisfreie Stadt for the two district forms.
        // (Aachen, Hanover, and Saarbrücken ride with rural_district:
        // Rural-form Kommunalverbände besonderer Art, district-level.)
        return [
            'state' => 'Land',
            'rural_district' => 'Landkreis',
            'urban_district' => 'Kreisfreie Stadt',
        ];
    }

    /** @return list<array{state_code: string, type_labels: array<string, string>}> */
    public function stateAreaTypeLabels(): array
    {
        // Stadtstaaten (Berlin, Hamburg, Bremen) are still Länder;
        // no per-state terminology override.
        return [];
    }

    /** @return array<string, list<array{role: string, country_code?: string, is_primary?: bool}>> */
    public function areaRoles(AddressCountry $country): array
    {
        $roles = [];

        foreach ($this->addressAreaSource()->areas() as $area) {
            $areaRoles = match ($area->type) {
                'state' => ['state'],
                'rural_district', 'urban_district' => ['district'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'DE', 'is_primary' => true],
                $areaRoles,
            );
        }

        return $roles;
    }

    /** @return array<string, list<array{name: string, name_type?: string, is_preferred?: bool}>> */
    public function areaNames(AddressCountry $country): array
    {
        return [
            'de:state:bayern' => [
                ['name' => 'Bavaria', 'name_type' => 'alternative'],
            ],
            'de:state:niedersachsen' => [
                ['name' => 'Lower Saxony', 'name_type' => 'alternative'],
            ],
            'de:state:nordrhein-westfalen' => [
                ['name' => 'North Rhine-Westphalia', 'name_type' => 'alternative'],
            ],
            'de:state:rheinland-pfalz' => [
                ['name' => 'Rhineland-Palatinate', 'name_type' => 'alternative'],
            ],
            'de:state:sachsen' => [
                ['name' => 'Saxony', 'name_type' => 'alternative'],
            ],
            'de:state:sachsen-anhalt' => [
                ['name' => 'Saxony-Anhalt', 'name_type' => 'alternative'],
            ],
            'de:state:thuringen' => [
                ['name' => 'Thuringia', 'name_type' => 'alternative'],
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
            __DIR__ . '/../../../resources/geography/germany-address-areas.csv',
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
            'BB' => 'BB',
            'BE' => 'BE',
            'BW' => 'BW',
            'BY' => 'BY',
            'HB' => 'HB',
            'HE' => 'HE',
            'HH' => 'HH',
            'MV' => 'MV',
            'NI' => 'NI',
            'NW' => 'NW',
            'RP' => 'RP',
            'SH' => 'SH',
            'SL' => 'SL',
            'SN' => 'SN',
            'ST' => 'ST',
            'TH' => 'TH',
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
            ['name' => 'Brandenburg', 'code' => 'BB'],
            ['name' => 'Berlin', 'code' => 'BE'],
            ['name' => 'Baden-Württemberg', 'code' => 'BW'],
            ['name' => 'Bayern', 'code' => 'BY'],
            ['name' => 'Bremen', 'code' => 'HB'],
            ['name' => 'Hessen', 'code' => 'HE'],
            ['name' => 'Hamburg', 'code' => 'HH'],
            ['name' => 'Mecklenburg-Vorpommern', 'code' => 'MV'],
            ['name' => 'Niedersachsen', 'code' => 'NI'],
            ['name' => 'Nordrhein-Westfalen', 'code' => 'NW'],
            ['name' => 'Rheinland-Pfalz', 'code' => 'RP'],
            ['name' => 'Schleswig-Holstein', 'code' => 'SH'],
            ['name' => 'Saarland', 'code' => 'SL'],
            ['name' => 'Sachsen', 'code' => 'SN'],
            ['name' => 'Sachsen-Anhalt', 'code' => 'ST'],
            ['name' => 'Thüringen', 'code' => 'TH'],
        ];
    }
}
