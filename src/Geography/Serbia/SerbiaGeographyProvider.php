<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Serbia;

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

class SerbiaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_serbia_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.serbia';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'RS';
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
                        key: 'district',
                        label: 'District / Province / City',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['district', 'province', 'city'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'municipality',
                        label: 'Municipality / City',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['municipality', 'city', 'city_municipality'],
                        areaLevels: [2],
                        parentKey: 'district',
                        assignmentRole: 'municipality',
                    ),
                ],
            ),
        ];
    }

    /** @return array<string, string> */
    public function areaTypeLabels(): array
    {
        // Serbian administrative terms in Latin script (`city` spans Belgrade at L1 and county cities at L2).
        return [
            'district' => 'Okrug',
            'province' => 'Pokrajina',
            'city' => 'Grad',
            'municipality' => 'Opština',
            'city_municipality' => 'Gradska opština',
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
            // `city` spans two levels: Belgrade (L1) vs county cities (L2).
            $areaRoles = match (true) {
                $area->type === 'district' => ['district'],
                $area->type === 'province' => ['province'],
                $area->type === 'city' && $area->level === 1 => ['city'],
                $area->type === 'city' => ['municipality'],
                $area->type === 'municipality' => ['municipality'],
                $area->type === 'city_municipality' => ['municipality'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'RS', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/serbia-address-areas.csv',
            self::AREA_SOURCE,
        );
    }

    /**
     * @return array<int|string, array{area_code: string, source: string, area_level: int, hierarchy_types?: list<string>}>
     */
    public function stateAreaMappings(): array
    {
        /** @var array<int|string, string> */
        $areaCodes = [
            '00' => '00',
            '14' => '14',
            '11' => '11',
            '02' => '02',
            '23' => '23',
            '09' => '09',
            '25' => '25',
            'KM' => 'KM',
            '29' => '29',
            '28' => '28',
            '08' => '08',
            '17' => '17',
            '20' => '20',
            '01' => '01',
            '03' => '03',
            '24' => '24',
            '26' => '26',
            '22' => '22',
            '10' => '10',
            '13' => '13',
            '27' => '27',
            '19' => '19',
            '18' => '18',
            '06' => '06',
            '04' => '04',
            '07' => '07',
            '12' => '12',
            '21' => '21',
            'VO' => 'VO',
            '05' => '05',
            '15' => '15',
            '16' => '16',
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
            ['name' => 'Belgrade', 'code' => '00'],
            ['name' => 'Bor', 'code' => '14'],
            ['name' => 'Braničevo', 'code' => '11'],
            ['name' => 'Central Banat', 'code' => '02'],
            ['name' => 'Jablanica', 'code' => '23'],
            ['name' => 'Kolubara', 'code' => '09'],
            ['name' => 'Kosovo', 'code' => '25'],
            ['name' => 'Kosovo-Metohija', 'code' => 'KM'],
            ['name' => 'Kosovo-Pomoravlje', 'code' => '29'],
            ['name' => 'Kosovska Mitrovica', 'code' => '28'],
            ['name' => 'Mačva', 'code' => '08'],
            ['name' => 'Moravica', 'code' => '17'],
            ['name' => 'Nišava', 'code' => '20'],
            ['name' => 'North Bačka', 'code' => '01'],
            ['name' => 'North Banat', 'code' => '03'],
            ['name' => 'Pčinja', 'code' => '24'],
            ['name' => 'Peć', 'code' => '26'],
            ['name' => 'Pirot', 'code' => '22'],
            ['name' => 'Podunavlje', 'code' => '10'],
            ['name' => 'Pomoravlje', 'code' => '13'],
            ['name' => 'Prizren', 'code' => '27'],
            ['name' => 'Rasina', 'code' => '19'],
            ['name' => 'Raška', 'code' => '18'],
            ['name' => 'South Bačka', 'code' => '06'],
            ['name' => 'South Banat', 'code' => '04'],
            ['name' => 'Srem', 'code' => '07'],
            ['name' => 'Šumadija', 'code' => '12'],
            ['name' => 'Toplica', 'code' => '21'],
            ['name' => 'Vojvodina', 'code' => 'VO'],
            ['name' => 'West Bačka', 'code' => '05'],
            ['name' => 'Zaječar', 'code' => '15'],
            ['name' => 'Zlatibor', 'code' => '16'],
        ];
    }
}
