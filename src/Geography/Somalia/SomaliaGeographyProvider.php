<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Somalia;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class SomaliaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_somalia_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.somalia';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'SO';
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
                        key: 'region',
                        label: 'Region',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['region'],
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
                'region' => ['region'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'SO', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/somalia-address-areas.csv',
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
            'AW' => 'AW',
            'BK' => 'BK',
            'BN' => 'BN',
            'BR' => 'BR',
            'BY' => 'BY',
            'GA' => 'GA',
            'GE' => 'GE',
            'HI' => 'HI',
            'JH' => 'JH',
            'SH' => 'SH',
            'JD' => 'JD',
            'SD' => 'SD',
            'MU' => 'MU',
            'NU' => 'NU',
            'SA' => 'SA',
            'SO' => 'SO',
            'TO' => 'TO',
            'WO' => 'WO',
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
            ['name' => 'Awdal', 'code' => 'AW'],
            ['name' => 'Bakool', 'code' => 'BK'],
            ['name' => 'Banaadir', 'code' => 'BN'],
            ['name' => 'Bari', 'code' => 'BR'],
            ['name' => 'Bay', 'code' => 'BY'],
            ['name' => 'Galguduud', 'code' => 'GA'],
            ['name' => 'Gedo', 'code' => 'GE'],
            ['name' => 'Hiran', 'code' => 'HI'],
            ['name' => 'Lower Juba', 'code' => 'JH'],
            ['name' => 'Lower Shebelle', 'code' => 'SH'],
            ['name' => 'Middle Juba', 'code' => 'JD'],
            ['name' => 'Middle Shebelle', 'code' => 'SD'],
            ['name' => 'Mudug', 'code' => 'MU'],
            ['name' => 'Nugal', 'code' => 'NU'],
            ['name' => 'Sanaag', 'code' => 'SA'],
            ['name' => 'Sool', 'code' => 'SO'],
            ['name' => 'Togdheer', 'code' => 'TO'],
            ['name' => 'Woqooyi Galbeed', 'code' => 'WO'],
        ];
    }
}
