<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\CapeVerde;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class CapeVerdeGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_cape_verde_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.cape_verde';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'CV';
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
                        label: 'Municipality / Geographical Region',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['municipality', 'geographical_region'],
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
                'municipality' => ['municipality'],
                'geographical_region' => ['geographical_region'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'CV', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/cape-verde-address-areas.csv',
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
            'B' => 'B',
            'BV' => 'BV',
            'BR' => 'BR',
            'MA' => 'MA',
            'MO' => 'MO',
            'PA' => 'PA',
            'PN' => 'PN',
            'PR' => 'PR',
            'RB' => 'RB',
            'RG' => 'RG',
            'RS' => 'RS',
            'SL' => 'SL',
            'CA' => 'CA',
            'CF' => 'CF',
            'CR' => 'CR',
            'SD' => 'SD',
            'SF' => 'SF',
            'SO' => 'SO',
            'SM' => 'SM',
            'SS' => 'SS',
            'SV' => 'SV',
            'S' => 'S',
            'TA' => 'TA',
            'TS' => 'TS',
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
            ['name' => 'Barlavento Islands', 'code' => 'B'],
            ['name' => 'Boa Vista', 'code' => 'BV'],
            ['name' => 'Brava', 'code' => 'BR'],
            ['name' => 'Maio', 'code' => 'MA'],
            ['name' => 'Mosteiros', 'code' => 'MO'],
            ['name' => 'Paul', 'code' => 'PA'],
            ['name' => 'Porto Novo', 'code' => 'PN'],
            ['name' => 'Praia', 'code' => 'PR'],
            ['name' => 'Ribeira Brava', 'code' => 'RB'],
            ['name' => 'Ribeira Grande', 'code' => 'RG'],
            ['name' => 'Ribeira Grande de Santiago', 'code' => 'RS'],
            ['name' => 'Sal', 'code' => 'SL'],
            ['name' => 'Santa Catarina', 'code' => 'CA'],
            ['name' => 'Santa Catarina do Fogo', 'code' => 'CF'],
            ['name' => 'Santa Cruz', 'code' => 'CR'],
            ['name' => 'São Domingos', 'code' => 'SD'],
            ['name' => 'São Filipe', 'code' => 'SF'],
            ['name' => 'São Lourenço dos Órgãos', 'code' => 'SO'],
            ['name' => 'São Miguel', 'code' => 'SM'],
            ['name' => 'São Salvador do Mundo', 'code' => 'SS'],
            ['name' => 'São Vicente', 'code' => 'SV'],
            ['name' => 'Sotavento Islands', 'code' => 'S'],
            ['name' => 'Tarrafal', 'code' => 'TA'],
            ['name' => 'Tarrafal de São Nicolau', 'code' => 'TS'],
        ];
    }
}
