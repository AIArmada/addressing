<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Chile;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class ChileGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_chile_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.chile';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'CL';
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
                'region' => ['region'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'CL', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/chile-address-areas.csv',
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
            'AI' => 'AI',
            'AN' => 'AN',
            'AP' => 'AP',
            'AT' => 'AT',
            'BI' => 'BI',
            'CO' => 'CO',
            'AR' => 'AR',
            'LI' => 'LI',
            'LL' => 'LL',
            'LR' => 'LR',
            'MA' => 'MA',
            'ML' => 'ML',
            'NB' => 'NB',
            'RM' => 'RM',
            'TA' => 'TA',
            'VS' => 'VS',
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
            ['name' => 'Aisén del General Carlos Ibañez del Campo', 'code' => 'AI'],
            ['name' => 'Antofagasta', 'code' => 'AN'],
            ['name' => 'Arica y Parinacota', 'code' => 'AP'],
            ['name' => 'Atacama', 'code' => 'AT'],
            ['name' => 'Biobío', 'code' => 'BI'],
            ['name' => 'Coquimbo', 'code' => 'CO'],
            ['name' => 'La Araucanía', 'code' => 'AR'],
            ['name' => 'Libertador General Bernardo O\'Higgins', 'code' => 'LI'],
            ['name' => 'Los Lagos', 'code' => 'LL'],
            ['name' => 'Los Ríos', 'code' => 'LR'],
            ['name' => 'Magallanes y de la Antártica Chilena', 'code' => 'MA'],
            ['name' => 'Maule', 'code' => 'ML'],
            ['name' => 'Ñuble', 'code' => 'NB'],
            ['name' => 'Región Metropolitana de Santiago', 'code' => 'RM'],
            ['name' => 'Tarapacá', 'code' => 'TA'],
            ['name' => 'Valparaíso', 'code' => 'VS'],
        ];
    }
}
