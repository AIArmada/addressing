<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Kenya;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class KenyaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_kenya_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.kenya';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'KE';
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
                        key: 'county',
                        label: 'County',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['county'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'constituency',
                        label: 'Constituency',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['constituency'],
                        areaLevels: [2],
                        parentKey: 'county',
                        assignmentRole: 'constituency',
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
                'county' => ['county'],
                'constituency' => ['constituency'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'KE', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/kenya-address-areas.csv',
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
            '11' => '11',
            '12' => '12',
            '13' => '13',
            '14' => '14',
            '15' => '15',
            '16' => '16',
            '17' => '17',
            '18' => '18',
            '19' => '19',
            '20' => '20',
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
            '45' => '45',
            '46' => '46',
            '47' => '47',
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
            ['name' => 'Baringo', 'code' => '01'],
            ['name' => 'Bomet', 'code' => '02'],
            ['name' => 'Bungoma', 'code' => '03'],
            ['name' => 'Busia', 'code' => '04'],
            ['name' => 'Elgeyo-Marakwet', 'code' => '05'],
            ['name' => 'Embu', 'code' => '06'],
            ['name' => 'Garissa', 'code' => '07'],
            ['name' => 'Homa Bay', 'code' => '08'],
            ['name' => 'Isiolo', 'code' => '09'],
            ['name' => 'Kajiado', 'code' => '10'],
            ['name' => 'Kakamega', 'code' => '11'],
            ['name' => 'Kericho', 'code' => '12'],
            ['name' => 'Kiambu', 'code' => '13'],
            ['name' => 'Kilifi', 'code' => '14'],
            ['name' => 'Kirinyaga', 'code' => '15'],
            ['name' => 'Kisii', 'code' => '16'],
            ['name' => 'Kisumu', 'code' => '17'],
            ['name' => 'Kitui', 'code' => '18'],
            ['name' => 'Kwale', 'code' => '19'],
            ['name' => 'Laikipia', 'code' => '20'],
            ['name' => 'Lamu', 'code' => '21'],
            ['name' => 'Machakos', 'code' => '22'],
            ['name' => 'Makueni', 'code' => '23'],
            ['name' => 'Mandera', 'code' => '24'],
            ['name' => 'Marsabit', 'code' => '25'],
            ['name' => 'Meru', 'code' => '26'],
            ['name' => 'Migori', 'code' => '27'],
            ['name' => 'Mombasa', 'code' => '28'],
            ['name' => 'Murang\'a', 'code' => '29'],
            ['name' => 'Nairobi City', 'code' => '30'],
            ['name' => 'Nakuru', 'code' => '31'],
            ['name' => 'Nandi', 'code' => '32'],
            ['name' => 'Narok', 'code' => '33'],
            ['name' => 'Nyamira', 'code' => '34'],
            ['name' => 'Nyandarua', 'code' => '35'],
            ['name' => 'Nyeri', 'code' => '36'],
            ['name' => 'Samburu', 'code' => '37'],
            ['name' => 'Siaya', 'code' => '38'],
            ['name' => 'Taita-Taveta', 'code' => '39'],
            ['name' => 'Tana River', 'code' => '40'],
            ['name' => 'Tharaka-Nithi', 'code' => '41'],
            ['name' => 'Trans Nzoia', 'code' => '42'],
            ['name' => 'Turkana', 'code' => '43'],
            ['name' => 'Uasin Gishu', 'code' => '44'],
            ['name' => 'Vihiga', 'code' => '45'],
            ['name' => 'Wajir', 'code' => '46'],
            ['name' => 'West Pokot', 'code' => '47'],
        ];
    }
}
