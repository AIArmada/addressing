<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Laos;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class LaosGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_laos_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.laos';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'LA';
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
                        label: 'Province / Prefecture',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['province', 'prefecture'],
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
                'prefecture' => ['province'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'LA', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/laos-address-areas.csv',
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
            'AT' => 'AT',
            'BK' => 'BK',
            'BL' => 'BL',
            'CH' => 'CH',
            'HO' => 'HO',
            'KH' => 'KH',
            'LM' => 'LM',
            'LP' => 'LP',
            'OU' => 'OU',
            'PH' => 'PH',
            'XA' => 'XA',
            'SL' => 'SL',
            'SV' => 'SV',
            'XE' => 'XE',
            'VI' => 'VI',
            'VT' => 'VT',
            'XS' => 'XS',
            'XI' => 'XI',
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
            ['name' => 'Attapeu', 'code' => 'AT'],
            ['name' => 'Bokeo', 'code' => 'BK'],
            ['name' => 'Bolikhamsai', 'code' => 'BL'],
            ['name' => 'Champasak', 'code' => 'CH'],
            ['name' => 'Houaphanh', 'code' => 'HO'],
            ['name' => 'Khammouane', 'code' => 'KH'],
            ['name' => 'Luang Namtha', 'code' => 'LM'],
            ['name' => 'Luang Prabang', 'code' => 'LP'],
            ['name' => 'Oudomxay', 'code' => 'OU'],
            ['name' => 'Phongsaly', 'code' => 'PH'],
            ['name' => 'Sainyabuli', 'code' => 'XA'],
            ['name' => 'Salavan', 'code' => 'SL'],
            ['name' => 'Savannakhet', 'code' => 'SV'],
            ['name' => 'Sekong', 'code' => 'XE'],
            ['name' => 'Vientiane', 'code' => 'VI'],
            ['name' => 'Vientiane', 'code' => 'VT'],
            ['name' => 'Xaisomboun', 'code' => 'XS'],
            ['name' => 'Xiangkhouang', 'code' => 'XI'],
        ];
    }
}
