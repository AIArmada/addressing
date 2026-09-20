<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Georgia;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class GeorgiaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_georgia_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.georgia';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'GE';
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
                        label: 'Region / Autonomous Republic / City',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['region', 'autonomous_republic', 'city'],
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
                'autonomous_republic' => ['autonomous_republic'],
                'city' => ['city'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'GE', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/georgia-address-areas.csv',
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
            'AJ' => 'AJ',
            'GU' => 'GU',
            'IM' => 'IM',
            'KA' => 'KA',
            'KK' => 'KK',
            'MM' => 'MM',
            'RL' => 'RL',
            'SZ' => 'SZ',
            'SJ' => 'SJ',
            'SK' => 'SK',
            'TB' => 'TB',
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
            ['name' => 'Abkhazia', 'code' => 'AB'],
            ['name' => 'Adjara', 'code' => 'AJ'],
            ['name' => 'Guria', 'code' => 'GU'],
            ['name' => 'Imereti', 'code' => 'IM'],
            ['name' => 'Kakheti', 'code' => 'KA'],
            ['name' => 'Kvemo Kartli', 'code' => 'KK'],
            ['name' => 'Mtskheta-Mtianeti', 'code' => 'MM'],
            ['name' => 'Racha-Lechkhumi and Kvemo Svaneti', 'code' => 'RL'],
            ['name' => 'Samegrelo-Zemo Svaneti', 'code' => 'SZ'],
            ['name' => 'Samtskhe-Javakheti', 'code' => 'SJ'],
            ['name' => 'Shida Kartli', 'code' => 'SK'],
            ['name' => 'Tbilisi', 'code' => 'TB'],
        ];
    }
}
