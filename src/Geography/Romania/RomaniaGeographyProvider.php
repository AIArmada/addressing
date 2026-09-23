<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Romania;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryAreaTypeLabelProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class RomaniaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_romania_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.romania';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'RO';
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
                        key: 'department',
                        label: 'Department / Municipality',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['department', 'municipality'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'commune',
                        label: 'Commune / Town / Municipality / Sector',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['commune', 'town', 'municipality', 'sector'],
                        areaLevels: [2],
                        parentKey: 'department',
                        assignmentRole: 'commune',
                    ),
                ],
            ),
        ];
    }

    /** @return array<string, string> */
    public function areaTypeLabels(): array
    {
        // Romanian administrative terms (`municipality` spans Bucharest at L1 and county municipalities at L2).
        return [
            'department' => 'Județ',
            'municipality' => 'Municipiu',
            'commune' => 'Comună',
            'town' => 'Oraș',
            'sector' => 'Sector',
        ];
    }

    /** @return list<array{state_code: string, type_labels: array<string, string>}> */
    public function stateAreaTypeLabels(): array
    {
        return [];
    }

    /** @return array<string, list<array{role: string, country_code?: string, is_primary?: bool}>> */
    public function areaRoles(AddressCountry $country): array
    {
        $roles = [];

        foreach ($this->addressAreaSource()->areas() as $area) {
            // `municipality` spans two levels: Bucharest (L1) vs county municipalities (L2).
            $areaRoles = match (true) {
                $area->type === 'department' => ['department'],
                $area->type === 'municipality' && $area->level === 1 => ['municipality'],
                $area->type === 'municipality' => ['commune'],
                $area->type === 'town' => ['commune'],
                $area->type === 'commune' => ['commune'],
                $area->type === 'sector' => ['commune'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'RO', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/romania-address-areas.csv',
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
            'AB' => 'AB',
            'AR' => 'AR',
            'AG' => 'AG',
            'BC' => 'BC',
            'BH' => 'BH',
            'BN' => 'BN',
            'BT' => 'BT',
            'BR' => 'BR',
            'BV' => 'BV',
            'B' => 'B',
            'BZ' => 'BZ',
            'CL' => 'CL',
            'CS' => 'CS',
            'CJ' => 'CJ',
            'CT' => 'CT',
            'CV' => 'CV',
            'DB' => 'DB',
            'DJ' => 'DJ',
            'GL' => 'GL',
            'GR' => 'GR',
            'GJ' => 'GJ',
            'HR' => 'HR',
            'HD' => 'HD',
            'IL' => 'IL',
            'IS' => 'IS',
            'IF' => 'IF',
            'MM' => 'MM',
            'MH' => 'MH',
            'MS' => 'MS',
            'NT' => 'NT',
            'OT' => 'OT',
            'PH' => 'PH',
            'SJ' => 'SJ',
            'SM' => 'SM',
            'SB' => 'SB',
            'SV' => 'SV',
            'TR' => 'TR',
            'TM' => 'TM',
            'TL' => 'TL',
            'VL' => 'VL',
            'VS' => 'VS',
            'VN' => 'VN',
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
            ['name' => 'Alba', 'code' => 'AB'],
            ['name' => 'Arad', 'code' => 'AR'],
            ['name' => 'Argeș', 'code' => 'AG'],
            ['name' => 'Bacău', 'code' => 'BC'],
            ['name' => 'Bihor', 'code' => 'BH'],
            ['name' => 'Bistrița-Năsăud', 'code' => 'BN'],
            ['name' => 'Botoșani', 'code' => 'BT'],
            ['name' => 'Brăila', 'code' => 'BR'],
            ['name' => 'Brașov', 'code' => 'BV'],
            ['name' => 'Bucharest', 'code' => 'B'],
            ['name' => 'Buzău', 'code' => 'BZ'],
            ['name' => 'Călărași', 'code' => 'CL'],
            ['name' => 'Caraș-Severin', 'code' => 'CS'],
            ['name' => 'Cluj', 'code' => 'CJ'],
            ['name' => 'Constanța', 'code' => 'CT'],
            ['name' => 'Covasna', 'code' => 'CV'],
            ['name' => 'Dâmbovița', 'code' => 'DB'],
            ['name' => 'Dolj', 'code' => 'DJ'],
            ['name' => 'Galați', 'code' => 'GL'],
            ['name' => 'Giurgiu', 'code' => 'GR'],
            ['name' => 'Gorj', 'code' => 'GJ'],
            ['name' => 'Harghita', 'code' => 'HR'],
            ['name' => 'Hunedoara', 'code' => 'HD'],
            ['name' => 'Ialomița', 'code' => 'IL'],
            ['name' => 'Iași', 'code' => 'IS'],
            ['name' => 'Ilfov', 'code' => 'IF'],
            ['name' => 'Maramureș', 'code' => 'MM'],
            ['name' => 'Mehedinți', 'code' => 'MH'],
            ['name' => 'Mureș', 'code' => 'MS'],
            ['name' => 'Neamț', 'code' => 'NT'],
            ['name' => 'Olt', 'code' => 'OT'],
            ['name' => 'Prahova', 'code' => 'PH'],
            ['name' => 'Sălaj', 'code' => 'SJ'],
            ['name' => 'Satu Mare', 'code' => 'SM'],
            ['name' => 'Sibiu', 'code' => 'SB'],
            ['name' => 'Suceava', 'code' => 'SV'],
            ['name' => 'Teleorman', 'code' => 'TR'],
            ['name' => 'Timiș', 'code' => 'TM'],
            ['name' => 'Tulcea', 'code' => 'TL'],
            ['name' => 'Vâlcea', 'code' => 'VL'],
            ['name' => 'Vaslui', 'code' => 'VS'],
            ['name' => 'Vrancea', 'code' => 'VN'],
        ];
    }
}
