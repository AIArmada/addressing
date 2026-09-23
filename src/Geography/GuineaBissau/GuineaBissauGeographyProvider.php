<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\GuineaBissau;

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

class GuineaBissauGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_guinea_bissau_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.guinea_bissau';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'GW';
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

        // Leste/Norte/Sul are statistical groupings, not administrative
        // states. Delete stragglers seeded before that fix.
        $stateClass::query()
            ->where('country_id', $country->id)
            ->whereIn('code', ['L', 'N', 'S'])
            ->delete();
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
                        label: 'Region / Autonomous Sector',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['region', 'autonomous_sector'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'sector',
                        label: 'Sector',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['sector'],
                        areaLevels: [2],
                        parentKey: 'region',
                        assignmentRole: 'sector',
                    ),
                ],
            ),
        ];
    }

    /** @return array<string, string> */
    public function areaTypeLabels(): array
    {
        // Portuguese administrative terms.
        return [
            'region' => 'Região',
            'autonomous_sector' => 'Sector Autónomo',
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
            $areaRoles = match ($area->type) {
                'region' => ['region'],
                'autonomous_sector' => ['autonomous_sector'],
                'sector' => ['sector'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'GW', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/guinea-bissau-address-areas.csv',
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
            'BA' => 'BA',
            'BM' => 'BM',
            'BS' => 'BS',
            'BL' => 'BL',
            'CA' => 'CA',
            'GA' => 'GA',
            'OI' => 'OI',
            'QU' => 'QU',
            'TO' => 'TO',
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
            ['name' => 'Bafatá', 'code' => 'BA'],
            ['name' => 'Biombo', 'code' => 'BM'],
            ['name' => 'Bissau', 'code' => 'BS'],
            ['name' => 'Bolama', 'code' => 'BL'],
            ['name' => 'Cacheu', 'code' => 'CA'],
            ['name' => 'Gabú', 'code' => 'GA'],
            ['name' => 'Oio', 'code' => 'OI'],
            ['name' => 'Quinara', 'code' => 'QU'],
            ['name' => 'Tombali', 'code' => 'TO'],
        ];
    }
}
