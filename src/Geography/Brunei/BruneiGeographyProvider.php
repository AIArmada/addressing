<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Brunei;

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

class BruneiGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_brunei_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.brunei';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'BN';
    }

    public function seed(AddressCountry $brunei): void
    {
        $stateClass = ModelResolver::stateClass();
        $statesData = $this->stateDefinitions();

        foreach ($statesData as $s) {
            $stateClass::updateOrCreate(
                ['country_id' => $brunei->id, 'code' => $s['code']],
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
                        label: 'District',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['district'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'mukim',
                        label: 'Mukim',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['mukim'],
                        areaLevels: [2],
                        parentKey: 'district',
                        assignmentRole: 'mukim',
                    ),
                ],
            ),
        ];
    }

    /** @return array<string, string> */
    public function areaTypeLabels(): array
    {
        // Malay administrative terms (daerah for districts).
        return [
            'district' => 'Daerah',
            'mukim' => 'Mukim',
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
                'district' => ['district'],
                'mukim' => ['mukim'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'BN', 'is_primary' => true],
                $areaRoles,
            );
        }

        return $roles;
    }

    /** @return array<string, list<array{name: string, name_type?: string, is_preferred?: bool}>> */
    public function areaNames(AddressCountry $country): array
    {
        return [
            'bn:district:brunei-muara' => [
                ['name' => 'Brunei Muara', 'name_type' => 'alternative'],
            ],
            'bn:mukim:pengkalan-batu' => [
                ['name' => 'Pangkalan Batu', 'name_type' => 'alternative'],
            ],
        ];
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
            __DIR__ . '/../../../resources/geography/brunei-address-areas.csv',
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
            'BE' => 'BE',
            'BM' => 'BM',
            'TE' => 'TE',
            'TU' => 'TU',
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
            ['name' => 'Belait', 'code' => 'BE'],
            ['name' => 'Brunei-Muara', 'code' => 'BM'],
            ['name' => 'Temburong', 'code' => 'TE'],
            ['name' => 'Tutong', 'code' => 'TU'],
        ];
    }
}
