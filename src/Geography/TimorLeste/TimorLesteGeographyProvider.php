<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\TimorLeste;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class TimorLesteGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_timor_leste_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.timor_leste';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'TL';
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
                        label: 'Municipality',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['municipality', 'special_administrative_region'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'administrative_post',
                        label: 'Administrative Post',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['administrative_post'],
                        areaLevels: [2],
                        parentKey: 'municipality',
                        assignmentRole: 'administrative_post',
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
                'special_administrative_region' => ['municipality'],
                'administrative_post' => ['administrative_post'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'TL', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/timor-leste-address-areas.csv',
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
            'AL' => 'AL',
            'AN' => 'AN',
            'BA' => 'BA',
            'BO' => 'BO',
            'CO' => 'CO',
            'DI' => 'DI',
            'ER' => 'ER',
            'LA' => 'LA',
            'LI' => 'LI',
            'MT' => 'MT',
            'MF' => 'MF',
            'OE' => 'OE',
            'VI' => 'VI',
            // Atauro has no ISO 3166-2 code yet; AT is provisional.
            'AT' => 'AT',
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
            ['name' => 'Aileu', 'code' => 'AL'],
            ['name' => 'Ainaro', 'code' => 'AN'],
            ['name' => 'Baucau', 'code' => 'BA'],
            ['name' => 'Bobonaro', 'code' => 'BO'],
            ['name' => 'Cova Lima', 'code' => 'CO'],
            ['name' => 'Dili', 'code' => 'DI'],
            ['name' => 'Ermera', 'code' => 'ER'],
            ['name' => 'Lautém', 'code' => 'LA'],
            ['name' => 'Liquiçá', 'code' => 'LI'],
            ['name' => 'Manatuto', 'code' => 'MT'],
            ['name' => 'Manufahi', 'code' => 'MF'],
            ['name' => 'Oecusse', 'code' => 'OE'],
            ['name' => 'Viqueque', 'code' => 'VI'],
            // Split from Dili 2022; ISO has not assigned a code yet.
            ['name' => 'Atauro', 'code' => 'AT'],
        ];
    }
}
