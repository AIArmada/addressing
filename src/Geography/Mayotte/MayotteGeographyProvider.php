<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Mayotte;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class MayotteGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_mayotte_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.mayotte';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'YT';
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
                        key: 'commune',
                        label: 'Commune',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['commune'],
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
                'commune' => ['commune'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'YT', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/mayotte-address-areas.csv',
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
            '14' => '14',
            '16' => '16',
            '05' => '05',
            '07' => '07',
            '11' => '11',
            '08' => '08',
            '04' => '04',
            '01' => '01',
            '06' => '06',
            '17' => '17',
            '13' => '13',
            '03' => '03',
            '15' => '15',
            '10' => '10',
            '02' => '02',
            '09' => '09',
            '12' => '12',
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
            ['name' => 'Acoua', 'code' => '14'],
            ['name' => 'Bandraboua', 'code' => '16'],
            ['name' => 'Bandrélé', 'code' => '05'],
            ['name' => 'Boueni', 'code' => '07'],
            ['name' => 'Chiconi', 'code' => '11'],
            ['name' => 'Chirongui', 'code' => '08'],
            ['name' => 'Dembeni', 'code' => '04'],
            ['name' => 'Dzaoudzi', 'code' => '01'],
            ['name' => 'Kani Keli', 'code' => '06'],
            ['name' => 'Koungou', 'code' => '17'],
            ['name' => 'M\'Tsangamouji', 'code' => '13'],
            ['name' => 'Mamoudzou', 'code' => '03'],
            ['name' => 'Mtsamboro', 'code' => '15'],
            ['name' => 'Ouangani', 'code' => '10'],
            ['name' => 'Pamandzi', 'code' => '02'],
            ['name' => 'Sada', 'code' => '09'],
            ['name' => 'Tsingoni', 'code' => '12'],
        ];
    }
}
