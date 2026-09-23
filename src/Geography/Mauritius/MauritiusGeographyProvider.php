<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Mauritius;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class MauritiusGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_mauritius_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.mauritius';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'MU';
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
                        label: 'District / Dependency',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['district', 'dependency'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'locality',
                        label: 'City / Town / Village',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['city', 'town', 'village'],
                        areaLevels: [2],
                        parentKey: 'district',
                        assignmentRole: 'locality',
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
                'district' => ['district'],
                'dependency' => ['dependency'],
                'city' => ['locality'],
                'town' => ['locality'],
                'village' => ['locality'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'MU', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/mauritius-address-areas.csv',
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
            'AG' => 'AG',
            'BL' => 'BL',
            'FL' => 'FL',
            'GP' => 'GP',
            'MO' => 'MO',
            'PA' => 'PA',
            'PW' => 'PW',
            'PL' => 'PL',
            'RR' => 'RR',
            'RO' => 'RO',
            'CC' => 'CC',
            'SA' => 'SA',
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
            ['name' => 'Agalega Islands', 'code' => 'AG'],
            ['name' => 'Black River', 'code' => 'BL'],
            ['name' => 'Flacq', 'code' => 'FL'],
            ['name' => 'Grand Port', 'code' => 'GP'],
            ['name' => 'Moka', 'code' => 'MO'],
            ['name' => 'Pamplemousses', 'code' => 'PA'],
            ['name' => 'Plaines Wilhems', 'code' => 'PW'],
            ['name' => 'Port Louis', 'code' => 'PL'],
            ['name' => 'Rivière du Rempart', 'code' => 'RR'],
            ['name' => 'Rodrigues Island', 'code' => 'RO'],
            ['name' => 'Saint Brandon Islands', 'code' => 'CC'],
            ['name' => 'Savanne', 'code' => 'SA'],
        ];
    }
}
