<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Angola;

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

class AngolaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider
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

        // Cuando Cubango split into Cuando and Cubango under the 2024 law.
        // Delete stragglers seeded before the split so reseeds converge.
        $stateClass::query()
            ->where('country_id', $country->id)
            ->where('code', 'CCU')
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
                        key: 'province',
                        label: 'Province',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['province'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'municipality',
                        label: 'Municipality',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['municipality'],
                        areaLevels: [2],
                        parentKey: 'province',
                        assignmentRole: 'municipality',
                    ),
                ],
            ),
        ];
    }

    /** @return array<string, string> */
    public function areaTypeLabels(): array
    {
        // Portuguese is the official language and the gazette names its
        // tiers Província and Município, so the labels use the Portuguese
        // terms rather than the English-generic headlines.
        return [
            'province' => 'Província',
            'municipality' => 'Município',
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
                'province' => ['province'],
                'municipality' => ['municipality'],
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
            'CNN' => 'CNN',
            'CNO' => 'CNO',
            'CUA' => 'CUA',
            'CUB' => 'CUB',
            'CUS' => 'CUS',
            'HUA' => 'HUA',
            'HUI' => 'HUI',
            'IEB' => 'IEB',
            'LNO' => 'LNO',
            'LSU' => 'LSU',
            'LUA' => 'LUA',
            'MAL' => 'MAL',
            'MLE' => 'MLE',
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
            ['name' => 'Cunene', 'code' => 'CNN'],
            ['name' => 'Cuanza Norte', 'code' => 'CNO'],
            ['name' => 'Cuando', 'code' => 'CUA'],
            ['name' => 'Cubango', 'code' => 'CUB'],
            ['name' => 'Cuanza Sul', 'code' => 'CUS'],
            ['name' => 'Huambo', 'code' => 'HUA'],
            ['name' => 'Huíla', 'code' => 'HUI'],
            ['name' => 'Icolo e Bengo', 'code' => 'IEB'],
            ['name' => 'Lunda Norte', 'code' => 'LNO'],
            ['name' => 'Lunda Sul', 'code' => 'LSU'],
            ['name' => 'Luanda', 'code' => 'LUA'],
            ['name' => 'Malanje', 'code' => 'MAL'],
            ['name' => 'Moxico Leste', 'code' => 'MLE'],
            ['name' => 'Moxico', 'code' => 'MOX'],
            ['name' => 'Namibe', 'code' => 'NAM'],
            ['name' => 'Uíge', 'code' => 'UIG'],
            ['name' => 'Zaire', 'code' => 'ZAI'],
        ];
    }
}
