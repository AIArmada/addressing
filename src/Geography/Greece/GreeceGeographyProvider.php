<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Greece;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class GreeceGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_greece_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.greece';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'GR';
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

        // Achaea (13) and East Attica (A2) are pre-2011 prefecture codes,
        // not current regions. Delete stragglers seeded before that fix.
        $stateClass::query()
            ->where('country_id', $country->id)
            ->whereIn('code', ['13', 'A2'])
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
                        key: 'administrative_region',
                        label: 'Administrative Region / Regional Unit',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['administrative_region', 'regional_unit'],
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
                'administrative_region' => ['administrative_region'],
                'regional_unit' => ['regional_unit'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'GR', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/greece-address-areas.csv',
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
            'I' => 'I',
            'H' => 'H',
            'B' => 'B',
            'M' => 'M',
            'A' => 'A',
            'D' => 'D',
            'F' => 'F',
            '69' => '69',
            'K' => 'K',
            'J' => 'J',
            'L' => 'L',
            'E' => 'E',
            'G' => 'G',
            'C' => 'C',
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
            ['name' => 'Attica', 'code' => 'I'],
            ['name' => 'Central Greece', 'code' => 'H'],
            ['name' => 'Central Macedonia', 'code' => 'B'],
            ['name' => 'Crete', 'code' => 'M'],
            ['name' => 'East Macedonia and Thrace', 'code' => 'A'],
            ['name' => 'Epirus', 'code' => 'D'],
            ['name' => 'Ionian Islands', 'code' => 'F'],
            ['name' => 'Mount Athos', 'code' => '69'],
            ['name' => 'North Aegean', 'code' => 'K'],
            ['name' => 'Peloponnese', 'code' => 'J'],
            ['name' => 'South Aegean', 'code' => 'L'],
            ['name' => 'Thessaly', 'code' => 'E'],
            ['name' => 'West Greece', 'code' => 'G'],
            ['name' => 'West Macedonia', 'code' => 'C'],
        ];
    }
}
