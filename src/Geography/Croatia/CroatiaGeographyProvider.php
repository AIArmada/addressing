<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Croatia;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class CroatiaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_croatia_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.croatia';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'HR';
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
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'HR', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/croatia-address-areas.csv',
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
            '07' => '07',
            '12' => '12',
            '21' => '21',
            '19' => '19',
            '18' => '18',
            '04' => '04',
            '06' => '06',
            '02' => '02',
            '09' => '09',
            '20' => '20',
            '14' => '14',
            '11' => '11',
            '08' => '08',
            '15' => '15',
            '03' => '03',
            '17' => '17',
            '05' => '05',
            '10' => '10',
            '16' => '16',
            '13' => '13',
            '01' => '01',
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
            ['name' => 'Bjelovar-Bilogora', 'code' => '07'],
            ['name' => 'Brod-Posavina', 'code' => '12'],
            ['name' => 'City of Zagreb', 'code' => '21'],
            ['name' => 'Dubrovnik-Neretva', 'code' => '19'],
            ['name' => 'Istria', 'code' => '18'],
            ['name' => 'Karlovac', 'code' => '04'],
            ['name' => 'Koprivnica-Križevci', 'code' => '06'],
            ['name' => 'Krapina-Zagorje', 'code' => '02'],
            ['name' => 'Lika-Senj', 'code' => '09'],
            ['name' => 'Međimurje', 'code' => '20'],
            ['name' => 'Osijek-Baranja', 'code' => '14'],
            ['name' => 'Požega-Slavonia', 'code' => '11'],
            ['name' => 'Primorje-Gorski Kotar', 'code' => '08'],
            ['name' => 'Šibenik-Knin', 'code' => '15'],
            ['name' => 'Sisak-Moslavina', 'code' => '03'],
            ['name' => 'Split-Dalmatia', 'code' => '17'],
            ['name' => 'Varaždin', 'code' => '05'],
            ['name' => 'Virovitica-Podravina', 'code' => '10'],
            ['name' => 'Vukovar-Syrmia', 'code' => '16'],
            ['name' => 'Zadar', 'code' => '13'],
            ['name' => 'Zagreb', 'code' => '01'],
        ];
    }
}
