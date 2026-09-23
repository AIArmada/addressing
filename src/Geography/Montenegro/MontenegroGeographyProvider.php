<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Montenegro;

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

class MontenegroGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_montenegro_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.montenegro';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'ME';
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
                        key: 'municipality',
                        label: 'Municipality',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['municipality'],
                        areaLevel: 1,
                    ),
                ],
            ),
        ];
    }

    /** @return array<string, string> */
    public function areaTypeLabels(): array
    {
        // Municipalities are opštine.
        return [
            'municipality' => 'Opština',
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
                'municipality' => ['municipality'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'ME', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/montenegro-address-areas.csv',
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
            '07' => '07',
            '22' => '22',
            '08' => '08',
            '09' => '09',
            '10' => '10',
            '11' => '11',
            '12' => '12',
            '06' => '06',
            '23' => '23',
            '13' => '13',
            '14' => '14',
            '15' => '15',
            '16' => '16',
            '17' => '17',
            '18' => '18',
            '19' => '19',
            '24' => '24',
            '20' => '20',
            '21' => '21',
            '25' => '25',
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
            ['name' => 'Andrijevica', 'code' => '01'],
            ['name' => 'Bar', 'code' => '02'],
            ['name' => 'Berane', 'code' => '03'],
            ['name' => 'Bijelo Polje', 'code' => '04'],
            ['name' => 'Budva', 'code' => '05'],
            ['name' => 'Danilovgrad', 'code' => '07'],
            ['name' => 'Gusinje', 'code' => '22'],
            ['name' => 'Herceg-Novi', 'code' => '08'],
            ['name' => 'Kolašin', 'code' => '09'],
            ['name' => 'Kotor', 'code' => '10'],
            ['name' => 'Mojkovac', 'code' => '11'],
            ['name' => 'Nikšić', 'code' => '12'],
            ['name' => 'Old Royal Capital Cetinje', 'code' => '06'],
            ['name' => 'Petnjica', 'code' => '23'],
            ['name' => 'Plav', 'code' => '13'],
            ['name' => 'Pljevlja', 'code' => '14'],
            ['name' => 'Plužine', 'code' => '15'],
            ['name' => 'Podgorica', 'code' => '16'],
            ['name' => 'Rožaje', 'code' => '17'],
            ['name' => 'Šavnik', 'code' => '18'],
            ['name' => 'Tivat', 'code' => '19'],
            ['name' => 'Tuzi', 'code' => '24'],
            ['name' => 'Ulcinj', 'code' => '20'],
            ['name' => 'Žabljak', 'code' => '21'],
            ['name' => 'Zeta', 'code' => '25'],
        ];
    }
}
