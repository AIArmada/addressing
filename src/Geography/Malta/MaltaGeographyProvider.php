<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Malta;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class MaltaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_malta_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.malta';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'MT';
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
                        key: 'local_council',
                        label: 'Local Council',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['local_council'],
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
                'local_council' => ['local_council'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'MT', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/malta-address-areas.csv',
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
            '01' => '01',
            '02' => '02',
            '03' => '03',
            '04' => '04',
            '05' => '05',
            '06' => '06',
            '07' => '07',
            '08' => '08',
            '09' => '09',
            '10' => '10',
            '13' => '13',
            '14' => '14',
            '15' => '15',
            '16' => '16',
            '17' => '17',
            '11' => '11',
            '12' => '12',
            '18' => '18',
            '19' => '19',
            '21' => '21',
            '22' => '22',
            '23' => '23',
            '24' => '24',
            '25' => '25',
            '26' => '26',
            '27' => '27',
            '28' => '28',
            '29' => '29',
            '30' => '30',
            '31' => '31',
            '32' => '32',
            '33' => '33',
            '34' => '34',
            '35' => '35',
            '36' => '36',
            '37' => '37',
            '38' => '38',
            '39' => '39',
            '40' => '40',
            '41' => '41',
            '42' => '42',
            '43' => '43',
            '44' => '44',
            '46' => '46',
            '47' => '47',
            '49' => '49',
            '50' => '50',
            '52' => '52',
            '53' => '53',
            '54' => '54',
            '20' => '20',
            '55' => '55',
            '56' => '56',
            '48' => '48',
            '51' => '51',
            '57' => '57',
            '58' => '58',
            '59' => '59',
            '60' => '60',
            '45' => '45',
            '61' => '61',
            '62' => '62',
            '63' => '63',
            '64' => '64',
            '65' => '65',
            '66' => '66',
            '67' => '67',
            '68' => '68',
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
            ['name' => 'Attard', 'code' => '01'],
            ['name' => 'Balzan', 'code' => '02'],
            ['name' => 'Birgu', 'code' => '03'],
            ['name' => 'Birkirkara', 'code' => '04'],
            ['name' => 'Birżebbuġa', 'code' => '05'],
            ['name' => 'Cospicua', 'code' => '06'],
            ['name' => 'Dingli', 'code' => '07'],
            ['name' => 'Fgura', 'code' => '08'],
            ['name' => 'Floriana', 'code' => '09'],
            ['name' => 'Fontana', 'code' => '10'],
            ['name' => 'Għajnsielem', 'code' => '13'],
            ['name' => 'Għarb', 'code' => '14'],
            ['name' => 'Għargħur', 'code' => '15'],
            ['name' => 'Għasri', 'code' => '16'],
            ['name' => 'Għaxaq', 'code' => '17'],
            ['name' => 'Gudja', 'code' => '11'],
            ['name' => 'Gżira', 'code' => '12'],
            ['name' => 'Ħamrun', 'code' => '18'],
            ['name' => 'Iklin', 'code' => '19'],
            ['name' => 'Kalkara', 'code' => '21'],
            ['name' => 'Kerċem', 'code' => '22'],
            ['name' => 'Kirkop', 'code' => '23'],
            ['name' => 'Lija', 'code' => '24'],
            ['name' => 'Luqa', 'code' => '25'],
            ['name' => 'Marsa', 'code' => '26'],
            ['name' => 'Marsaskala', 'code' => '27'],
            ['name' => 'Marsaxlokk', 'code' => '28'],
            ['name' => 'Mdina', 'code' => '29'],
            ['name' => 'Mellieħa', 'code' => '30'],
            ['name' => 'Mġarr', 'code' => '31'],
            ['name' => 'Mosta', 'code' => '32'],
            ['name' => 'Mqabba', 'code' => '33'],
            ['name' => 'Msida', 'code' => '34'],
            ['name' => 'Mtarfa', 'code' => '35'],
            ['name' => 'Munxar', 'code' => '36'],
            ['name' => 'Nadur', 'code' => '37'],
            ['name' => 'Naxxar', 'code' => '38'],
            ['name' => 'Paola', 'code' => '39'],
            ['name' => 'Pembroke', 'code' => '40'],
            ['name' => 'Pietà', 'code' => '41'],
            ['name' => 'Qala', 'code' => '42'],
            ['name' => 'Qormi', 'code' => '43'],
            ['name' => 'Qrendi', 'code' => '44'],
            ['name' => 'Rabat', 'code' => '46'],
            ['name' => 'Safi', 'code' => '47'],
            ['name' => 'San Ġwann', 'code' => '49'],
            ['name' => 'San Lawrenz', 'code' => '50'],
            ['name' => 'Sannat', 'code' => '52'],
            ['name' => 'Santa Luċija', 'code' => '53'],
            ['name' => 'Santa Venera', 'code' => '54'],
            ['name' => 'Senglea', 'code' => '20'],
            ['name' => 'Siġġiewi', 'code' => '55'],
            ['name' => 'Sliema', 'code' => '56'],
            ['name' => 'St. Julian\'s', 'code' => '48'],
            ['name' => 'St. Paul\'s Bay', 'code' => '51'],
            ['name' => 'Swieqi', 'code' => '57'],
            ['name' => 'Ta\' Xbiex', 'code' => '58'],
            ['name' => 'Tarxien', 'code' => '59'],
            ['name' => 'Valletta', 'code' => '60'],
            ['name' => 'Victoria', 'code' => '45'],
            ['name' => 'Xagħra', 'code' => '61'],
            ['name' => 'Xewkija', 'code' => '62'],
            ['name' => 'Xgħajra', 'code' => '63'],
            ['name' => 'Żabbar', 'code' => '64'],
            ['name' => 'Żebbuġ Gozo', 'code' => '65'],
            ['name' => 'Żebbuġ Malta', 'code' => '66'],
            ['name' => 'Żejtun', 'code' => '67'],
            ['name' => 'Żurrieq', 'code' => '68'],
        ];
    }
}
