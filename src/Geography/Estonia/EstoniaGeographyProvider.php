<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Estonia;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class EstoniaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_estonia_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.estonia';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'EE';
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
                    new AddressLevelDefinition(
                        key: 'municipality',
                        label: 'Municipality',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['rural_municipality', 'urban_municipality'],
                        areaLevels: [2],
                        parentKey: 'county',
                        assignmentRole: 'municipality',
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
                'rural_municipality' => ['municipality'],
                'urban_municipality' => ['municipality'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'EE', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/estonia-address-areas.csv',
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
            '37' => '37',
            '39' => '39',
            '45' => '45',
            '52' => '52',
            '50' => '50',
            '56' => '56',
            '60' => '60',
            '68' => '68',
            '64' => '64',
            '71' => '71',
            '74' => '74',
            '79' => '79',
            '81' => '81',
            '84' => '84',
            '87' => '87',
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
            ['name' => 'Harju', 'code' => '37'],
            ['name' => 'Hiiu', 'code' => '39'],
            ['name' => 'Ida-Viru', 'code' => '45'],
            ['name' => 'Järva', 'code' => '52'],
            ['name' => 'Jõgeva', 'code' => '50'],
            ['name' => 'Lääne', 'code' => '56'],
            ['name' => 'Lääne-Viru', 'code' => '60'],
            ['name' => 'Pärnu', 'code' => '68'],
            ['name' => 'Põlva', 'code' => '64'],
            ['name' => 'Rapla', 'code' => '71'],
            ['name' => 'Saare', 'code' => '74'],
            ['name' => 'Tartu', 'code' => '79'],
            ['name' => 'Valga', 'code' => '81'],
            ['name' => 'Viljandi', 'code' => '84'],
            ['name' => 'Võru', 'code' => '87'],
        ];
    }
}
