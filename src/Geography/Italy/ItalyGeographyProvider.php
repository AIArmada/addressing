<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Italy;

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

class ItalyGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_italy_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.italy';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'IT';
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
                        key: 'region',
                        label: 'Region',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['region'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'province',
                        label: 'Province / Metropolitan City / Consortium / Entity',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['province', 'metropolitan_city', 'free_municipal_consortium', 'decentralization_entity', 'autonomous_province'],
                        areaLevels: [2],
                        parentKey: 'region',
                        assignmentRole: 'province',
                    ),
                ],
            ),
        ];
    }

    /** @return array<string, string> */
    public function areaTypeLabels(): array
    {
        // The second-level types are already Italian administrative terms;
        // only the English-generic region type needs its proper term.
        return [
            'region' => 'Regione',
        ];
    }

    /** @return list<array{state_code: string, type_labels: array<string, string>}> */
    public function stateAreaTypeLabels(): array
    {
        return [];
    }

    /** @return array<string, list<array{role: string, country_code?: string, is_primary?: bool}>> */
    public function areaRoles(AddressCountry $country): array
    {
        $roles = [];

        foreach ($this->addressAreaSource()->areas() as $area) {
            $areaRoles = match ($area->type) {
                'region' => ['region'],
                'province' => ['province'],
                'metropolitan_city' => ['province'],
                'free_municipal_consortium' => ['province'],
                'decentralization_entity' => ['province'],
                'autonomous_province' => ['province'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'IT', 'is_primary' => true],
                $areaRoles,
            );
        }

        return $roles;
    }

    /** @return array<string, list<array{name: string, name_type?: string, is_preferred?: bool}>> */
    public function areaNames(AddressCountry $country): array
    {
        return [
            'it:region:piemonte' => [
                ['name' => 'Piedmont', 'name_type' => 'alternative'],
            ],
            'it:region:valle-d-aosta' => [
                ['name' => 'Aosta Valley', 'name_type' => 'alternative'],
            ],
            'it:region:lombardia' => [
                ['name' => 'Lombardy', 'name_type' => 'alternative'],
            ],
            'it:region:trentino-alto-adige' => [
                ['name' => 'Trentino-South Tyrol', 'name_type' => 'alternative'],
            ],
            'it:region:toscana' => [
                ['name' => 'Tuscany', 'name_type' => 'alternative'],
            ],
            'it:region:puglia' => [
                ['name' => 'Apulia', 'name_type' => 'alternative'],
            ],
            'it:region:sicilia' => [
                ['name' => 'Sicily', 'name_type' => 'alternative'],
            ],
            'it:region:sardegna' => [
                ['name' => 'Sardinia', 'name_type' => 'alternative'],
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
            __DIR__ . '/../../../resources/geography/italy-address-areas.csv',
            self::AREA_SOURCE,
        );
    }

    /**
     * @return array<int, array{area_code: string, source: string, area_level: int, hierarchy_types?: list<string>}>
     */
    public function stateAreaMappings(): array
    {
        /** @var array<int, string> */
        $areaCodes = [
            '21' => '21',
            '23' => '23',
            '25' => '25',
            '32' => '32',
            '34' => '34',
            '36' => '36',
            '42' => '42',
            '45' => '45',
            '52' => '52',
            '55' => '55',
            '57' => '57',
            '62' => '62',
            '65' => '65',
            '67' => '67',
            '72' => '72',
            '75' => '75',
            '77' => '77',
            '78' => '78',
            '82' => '82',
            '88' => '88',
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
            ['name' => 'Piemonte', 'code' => '21'],
            ['name' => 'Valle d\'Aosta', 'code' => '23'],
            ['name' => 'Lombardia', 'code' => '25'],
            ['name' => 'Trentino-Alto Adige', 'code' => '32'],
            ['name' => 'Veneto', 'code' => '34'],
            ['name' => 'Friuli-Venezia Giulia', 'code' => '36'],
            ['name' => 'Liguria', 'code' => '42'],
            ['name' => 'Emilia-Romagna', 'code' => '45'],
            ['name' => 'Toscana', 'code' => '52'],
            ['name' => 'Umbria', 'code' => '55'],
            ['name' => 'Marche', 'code' => '57'],
            ['name' => 'Lazio', 'code' => '62'],
            ['name' => 'Abruzzo', 'code' => '65'],
            ['name' => 'Molise', 'code' => '67'],
            ['name' => 'Campania', 'code' => '72'],
            ['name' => 'Puglia', 'code' => '75'],
            ['name' => 'Basilicata', 'code' => '77'],
            ['name' => 'Calabria', 'code' => '78'],
            ['name' => 'Sicilia', 'code' => '82'],
            ['name' => 'Sardegna', 'code' => '88'],
        ];
    }
}
