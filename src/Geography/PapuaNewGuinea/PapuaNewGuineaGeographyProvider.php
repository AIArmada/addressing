<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\PapuaNewGuinea;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class PapuaNewGuineaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_papua_new_guinea_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.papua_new_guinea';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'PG';
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
                        label: 'Province / Autonomous Region / District',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['province', 'autonomous_region', 'district'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'district',
                        label: 'District',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['district'],
                        areaLevels: [2],
                        parentKey: 'province',
                        assignmentRole: 'district',
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
                'autonomous_region' => ['autonomous_region'],
                'district' => ['district'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'PG', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/papua-new-guinea-address-areas.csv',
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
            'NSB' => 'NSB',
            'CPM' => 'CPM',
            'CPK' => 'CPK',
            'EBR' => 'EBR',
            'ESW' => 'ESW',
            'EHG' => 'EHG',
            'EPW' => 'EPW',
            'GPK' => 'GPK',
            'HLA' => 'HLA',
            'JWK' => 'JWK',
            'MPM' => 'MPM',
            'MRL' => 'MRL',
            'MBA' => 'MBA',
            'MPL' => 'MPL',
            'NIK' => 'NIK',
            'NPP' => 'NPP',
            'NCD' => 'NCD',
            'SAN' => 'SAN',
            'SHM' => 'SHM',
            'WBK' => 'WBK',
            'WPD' => 'WPD',
            'WHM' => 'WHM',
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
            ['name' => 'Bougainville', 'code' => 'NSB'],
            ['name' => 'Central', 'code' => 'CPM'],
            ['name' => 'Chimbu', 'code' => 'CPK'],
            ['name' => 'East New Britain', 'code' => 'EBR'],
            ['name' => 'East Sepik', 'code' => 'ESW'],
            ['name' => 'Eastern Highlands', 'code' => 'EHG'],
            ['name' => 'Enga', 'code' => 'EPW'],
            ['name' => 'Gulf', 'code' => 'GPK'],
            ['name' => 'Hela', 'code' => 'HLA'],
            ['name' => 'Jiwaka', 'code' => 'JWK'],
            ['name' => 'Madang', 'code' => 'MPM'],
            ['name' => 'Manus', 'code' => 'MRL'],
            ['name' => 'Milne Bay', 'code' => 'MBA'],
            ['name' => 'Morobe', 'code' => 'MPL'],
            ['name' => 'New Ireland', 'code' => 'NIK'],
            ['name' => 'Oro', 'code' => 'NPP'],
            ['name' => 'Port Moresby', 'code' => 'NCD'],
            ['name' => 'Sandaun', 'code' => 'SAN'],
            ['name' => 'Southern Highlands', 'code' => 'SHM'],
            ['name' => 'West New Britain', 'code' => 'WBK'],
            ['name' => 'Western', 'code' => 'WPD'],
            ['name' => 'Western Highlands', 'code' => 'WHM'],
        ];
    }
}
