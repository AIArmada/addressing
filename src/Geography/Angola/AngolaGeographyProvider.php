<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Angola;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class AngolaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_angola_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.angola';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'AO';
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
                        key: 'province',
                        label: 'Province',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['province'],
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
                'province' => ['province'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'AO', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/angola-address-areas.csv',
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
            'BGO' => 'BGO',
            'BGU' => 'BGU',
            'BIE' => 'BIE',
            'CAB' => 'CAB',
            'CCU' => 'CCU',
            'CNN' => 'CNN',
            'CNO' => 'CNO',
            'CUS' => 'CUS',
            'HUA' => 'HUA',
            'HUI' => 'HUI',
            'LNO' => 'LNO',
            'LSU' => 'LSU',
            'LUA' => 'LUA',
            'MAL' => 'MAL',
            'MOX' => 'MOX',
            'NAM' => 'NAM',
            'UIG' => 'UIG',
            'ZAI' => 'ZAI',
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
            ['name' => 'Bengo', 'code' => 'BGO'],
            ['name' => 'Benguela', 'code' => 'BGU'],
            ['name' => 'Bié', 'code' => 'BIE'],
            ['name' => 'Cabinda', 'code' => 'CAB'],
            ['name' => 'Cuando Cubango', 'code' => 'CCU'],
            ['name' => 'Cunene', 'code' => 'CNN'],
            ['name' => 'Cuanza Norte', 'code' => 'CNO'],
            ['name' => 'Cuanza', 'code' => 'CUS'],
            ['name' => 'Huambo', 'code' => 'HUA'],
            ['name' => 'Huíla', 'code' => 'HUI'],
            ['name' => 'Lunda Norte', 'code' => 'LNO'],
            ['name' => 'Lunda Sul', 'code' => 'LSU'],
            ['name' => 'Luanda', 'code' => 'LUA'],
            ['name' => 'Malanje', 'code' => 'MAL'],
            ['name' => 'Moxico', 'code' => 'MOX'],
            ['name' => 'Namibe', 'code' => 'NAM'],
            ['name' => 'Uíge', 'code' => 'UIG'],
            ['name' => 'Zaire', 'code' => 'ZAI'],
        ];
    }
}
