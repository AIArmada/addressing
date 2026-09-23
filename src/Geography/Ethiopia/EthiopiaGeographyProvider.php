<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Ethiopia;

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

class EthiopiaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_ethiopia_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.ethiopia';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'ET';
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

        // The Southern Nations, Nationalities, and Peoples' Region was
        // dissolved in August 2023 (split into Sidama, Southwest, South,
        // and Central Ethiopia). Delete stragglers seeded before that fix.
        $stateClass::query()
            ->where('country_id', $country->id)
            ->where('code', 'SN')
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
                        label: 'Region / City Administration',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['region', 'city'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'zone',
                        label: 'Zone / Woreda',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['zone', 'woreda'],
                        areaLevels: [2],
                        parentKey: 'region',
                        assignmentRole: 'zone',
                    ),
                ],
            ),
        ];
    }

    /** @return array<string, string> */
    public function areaTypeLabels(): array
    {
        // Regions are kilils; zones, woredas and cities keep the English headlines.
        return [
            'region' => 'Kilil',
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
                'city' => ['region'],
                'zone' => ['zone'],
                'woreda' => ['zone'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'ET', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/ethiopia-address-areas.csv',
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
            'AA' => 'AA',
            'AF' => 'AF',
            'AM' => 'AM',
            'BE' => 'BE',
            'CE' => 'CE',
            'DD' => 'DD',
            'GA' => 'GA',
            'HA' => 'HA',
            'OR' => 'OR',
            'SE' => 'SE',
            'SI' => 'SI',
            'SO' => 'SO',
            'SW' => 'SW',
            'TI' => 'TI',
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
            ['name' => 'Addis Ababa', 'code' => 'AA'],
            ['name' => 'Afar', 'code' => 'AF'],
            ['name' => 'Amhara', 'code' => 'AM'],
            ['name' => 'Benishangul-Gumuz', 'code' => 'BE'],
            ['name' => 'Central Ethiopia', 'code' => 'CE'],
            ['name' => 'Dire Dawa', 'code' => 'DD'],
            ['name' => 'Gambela', 'code' => 'GA'],
            ['name' => 'Harari', 'code' => 'HA'],
            ['name' => 'Oromia', 'code' => 'OR'],
            ['name' => 'South Ethiopia', 'code' => 'SE'],
            ['name' => 'Sidama', 'code' => 'SI'],
            ['name' => 'Somali', 'code' => 'SO'],
            ['name' => 'Southwest Ethiopia', 'code' => 'SW'],
            ['name' => 'Tigray', 'code' => 'TI'],
        ];
    }
}
