<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Malaysia;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class MalaysiaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    private const string AREA_SOURCE = 'aiarmada_addressing_malaysia_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.malaysia';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'MY';
    }

    public function seed(AddressCountry $malaysia): void
    {
        $stateClass = ModelResolver::stateClass();
        $statesData = $this->stateDefinitions();

        foreach ($statesData as $s) {
            $stateClass::updateOrCreate(
                ['country_id' => $malaysia->id, 'code' => $s['code']],
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
                key: 'postal',
                label: 'Postal / Address Geography',
                levels: [
                    new AddressLevelDefinition(
                        key: 'region',
                        label: 'State / Federal Territory',
                        kind: 'state',
                        hierarchyType: 'postal',
                        areaTypes: ['state', 'wilayah_persekutuan'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'locality',
                        label: 'Locality / Precinct / Kampung',
                        kind: 'area',
                        hierarchyType: 'postal',
                        areaTypes: ['locality', 'precinct'],
                        areaLevels: [2],
                        parentKey: 'region',
                        assignmentRole: 'postal_locality',
                    ),
                ],
            ),
            new AddressHierarchyDefinition(
                key: 'administrative',
                label: 'Administrative / Land Geography',
                levels: [
                    new AddressLevelDefinition(
                        key: 'region',
                        label: 'State / Federal Territory',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['state', 'wilayah_persekutuan'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'division',
                        label: 'Division / Bahagian',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['division'],
                        areaLevel: 2,
                        parentKey: 'region',
                        assignmentRole: 'administrative_division',
                    ),
                    new AddressLevelDefinition(
                        key: 'district',
                        label: 'District / Jajahan / Jajahan Kecil',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['district', 'minor_district'],
                        areaLevels: [2, 3],
                        parentKey: 'region',
                        assignmentRole: 'administrative_district',
                    ),
                    new AddressLevelDefinition(
                        key: 'subdivision',
                        label: 'Mukim / Subdistrict / Bandar / Pekan',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['city', 'municipality', 'mukim', 'subdistrict'],
                        areaLevels: [2, 3, 4],
                        parentKey: 'region',
                        assignmentRole: 'administrative_subdivision',
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
                'state', 'wilayah_persekutuan' => ['region'],
                'division' => ['administrative_division'],
                'district', 'minor_district' => ['administrative_district'],
                'city', 'municipality', 'mukim', 'subdistrict' => ['administrative_subdivision'],
                'precinct', 'locality' => ['postal_locality'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'MY', 'is_primary' => true],
                $areaRoles,
            );
        }

        return $roles;
    }

    /** @return array<string, list<array{name: string, name_type?: string, is_preferred?: bool}>> */
    public function areaNames(AddressCountry $country): array
    {
        return [
            'my:state:wilayah-persekutuan-kuala-lumpur' => [
                ['name' => 'Kuala Lumpur', 'name_type' => 'common', 'is_preferred' => true],
                ['name' => 'KL', 'name_type' => 'abbreviation'],
            ],
            'my:state:wilayah-persekutuan-putrajaya' => [
                ['name' => 'Putrajaya', 'name_type' => 'common', 'is_preferred' => true],
            ],
            'my:state:wilayah-persekutuan-labuan' => [
                ['name' => 'Labuan', 'name_type' => 'common', 'is_preferred' => true],
            ],
        ];
    }

    /** @return array<string, list<array{parent_source_id: string, relationship_type: string, hierarchy_type: string}>> */
    public function areaRelationships(AddressCountry $country): array
    {
        $relationships = [];
        $areas = $this->addressAreaSource()->areas()->all();
        $parentSourceIds = [];

        foreach ($areas as $area) {
            $parentSourceIds[$area->sourceId] = $area->parentSourceId;
        }

        foreach ($areas as $area) {
            if ($area->parentSourceId === null) {
                continue;
            }

            $isPostal = in_array($area->type, ['locality', 'precinct'], true);
            $hierarchyType = $isPostal ? 'postal' : 'administrative';

            $relationships[$area->sourceId][] = [
                'parent_source_id' => $area->parentSourceId,
                'relationship_type' => 'contains',
                'hierarchy_type' => $hierarchyType,
            ];

            if ($isPostal) {
                continue;
            }

            $stateSourceId = $this->stateAncestorSourceId($area->sourceId, $parentSourceIds);

            if ($stateSourceId !== null && $stateSourceId !== $area->parentSourceId) {
                $relationships[$area->sourceId][] = [
                    'parent_source_id' => $stateSourceId,
                    'relationship_type' => 'contains',
                    'hierarchy_type' => 'administrative',
                ];
            }
        }

        return $relationships;
    }

    /**
     * @param  array<string, string|null>  $parentSourceIds
     */
    private function stateAncestorSourceId(string $sourceId, array $parentSourceIds): ?string
    {
        $visited = [];
        $currentSourceId = $sourceId;

        while (isset($parentSourceIds[$currentSourceId])) {
            $parentSourceId = $parentSourceIds[$currentSourceId];

            if (isset($visited[$parentSourceId])) {
                return null;
            }

            if (str_starts_with($parentSourceId, 'my:state:')) {
                return $parentSourceId;
            }

            $visited[$parentSourceId] = true;
            $currentSourceId = $parentSourceId;
        }

        return null;
    }

    public function addressAreaSource(): AddressAreaSource
    {
        return new CsvAddressAreaSource(
            __DIR__ . '/../../../resources/geography/malaysia-address-areas.csv',
            self::AREA_SOURCE,
        );
    }

    /**
     * @return array<string, array{area_code: string, source: string, area_level: int, hierarchy_types: list<string>}>
     */
    public function stateAreaMappings(): array
    {
        /** @var array<string, string> */
        $areaCodes = [
            '01' => 'johor',
            '02' => 'kedah',
            '03' => 'kelantan',
            '04' => 'melaka',
            '05' => 'negeri-sembilan',
            '06' => 'pahang',
            '07' => 'pulau-pinang',
            '08' => 'perak',
            '09' => 'perlis',
            '10' => 'selangor',
            '11' => 'terengganu',
            '12' => 'sabah',
            '13' => 'sarawak',
            '14' => 'wp-kuala-lumpur',
            '15' => 'wp-labuan',
            '16' => 'wp-putrajaya',
        ];

        return array_map(
            static fn (string $areaCode): array => [
                'area_code' => $areaCode,
                'source' => self::AREA_SOURCE,
                'area_level' => 1,
                'hierarchy_types' => ['postal', 'administrative'],
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
            ['name' => 'Johor', 'code' => '01'],
            ['name' => 'Kedah', 'code' => '02'],
            ['name' => 'Kelantan', 'code' => '03'],
            ['name' => 'Melaka', 'code' => '04'],
            ['name' => 'Negeri Sembilan', 'code' => '05'],
            ['name' => 'Pahang', 'code' => '06'],
            ['name' => 'Perak', 'code' => '08'],
            ['name' => 'Perlis', 'code' => '09'],
            ['name' => 'Pulau Pinang', 'code' => '07'],
            ['name' => 'Sabah', 'code' => '12'],
            ['name' => 'Sarawak', 'code' => '13'],
            ['name' => 'Selangor', 'code' => '10'],
            ['name' => 'Terengganu', 'code' => '11'],
            ['name' => 'WP Kuala Lumpur', 'code' => '14'],
            ['name' => 'WP Labuan', 'code' => '15'],
            ['name' => 'WP Putrajaya', 'code' => '16'],
        ];
    }
}
