<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Luxembourg;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class LuxembourgGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_luxembourg_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.luxembourg';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'LU';
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

        // Single-letter district codes G/L were replaced by canton codes
        // GR/LU. Delete stragglers seeded before that fix.
        $stateClass::query()
            ->where('country_id', $country->id)
            ->whereIn('code', ['G', 'L'])
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
                        key: 'canton',
                        label: 'Canton',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['canton'],
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
                'canton' => ['canton'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'LU', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/luxembourg-address-areas.csv',
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
            'CA' => 'CA',
            'CL' => 'CL',
            'DI' => 'DI',
            'EC' => 'EC',
            'ES' => 'ES',
            'GR' => 'GR',
            'LU' => 'LU',
            'ME' => 'ME',
            'RD' => 'RD',
            'RM' => 'RM',
            'VD' => 'VD',
            'WI' => 'WI',
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
            ['name' => 'Capellen', 'code' => 'CA'],
            ['name' => 'Clervaux', 'code' => 'CL'],
            ['name' => 'Diekirch', 'code' => 'DI'],
            ['name' => 'Echternach', 'code' => 'EC'],
            ['name' => 'Esch-sur-Alzette', 'code' => 'ES'],
            ['name' => 'Grevenmacher', 'code' => 'GR'],
            ['name' => 'Luxembourg', 'code' => 'LU'],
            ['name' => 'Mersch', 'code' => 'ME'],
            ['name' => 'Redange', 'code' => 'RD'],
            ['name' => 'Remich', 'code' => 'RM'],
            ['name' => 'Vianden', 'code' => 'VD'],
            ['name' => 'Wiltz', 'code' => 'WI'],
        ];
    }
}
