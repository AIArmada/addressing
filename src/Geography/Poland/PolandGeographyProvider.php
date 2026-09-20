<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Poland;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class PolandGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_poland_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.poland';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'PL';
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
                        key: 'voivodeship',
                        label: 'Voivodeship',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['voivodeship'],
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
                'voivodeship' => ['voivodeship'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'PL', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/poland-address-areas.csv',
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
            '02' => '02',
            '04' => '04',
            '06' => '06',
            '08' => '08',
            '10' => '10',
            '12' => '12',
            '14' => '14',
            '16' => '16',
            '18' => '18',
            '20' => '20',
            '22' => '22',
            '24' => '24',
            '26' => '26',
            '28' => '28',
            '30' => '30',
            '32' => '32',
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
            ['name' => 'Lower Silesia', 'code' => '02'],
            ['name' => 'Kuyavia-Pomerania', 'code' => '04'],
            ['name' => 'Lublin', 'code' => '06'],
            ['name' => 'Lubusz', 'code' => '08'],
            ['name' => 'Łódź', 'code' => '10'],
            ['name' => 'Lesser Poland', 'code' => '12'],
            ['name' => 'Mazovia', 'code' => '14'],
            ['name' => 'Upper Silesia', 'code' => '16'],
            ['name' => 'Subcarpathia', 'code' => '18'],
            ['name' => 'Podlaskie', 'code' => '20'],
            ['name' => 'Pomerania', 'code' => '22'],
            ['name' => 'Silesia', 'code' => '24'],
            ['name' => 'Holy Cross', 'code' => '26'],
            ['name' => 'Warmia-Masuria', 'code' => '28'],
            ['name' => 'Greater Poland', 'code' => '30'],
            ['name' => 'West Pomerania', 'code' => '32'],
        ];
    }
}
