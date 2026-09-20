<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Iraq;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class IraqGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_iraq_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.iraq';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'IQ';
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

        // Iqlim Kurdistan is an autonomous region overlapping Erbil, Dohuk,
        // Sulaymaniyah, and Halabja — not a governorate. Delete stragglers
        // seeded before that fix.
        $stateClass::query()
            ->where('country_id', $country->id)
            ->where('code', 'KR')
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
                        key: 'governorate',
                        label: 'Governorate',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['governorate'],
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
                'governorate' => ['governorate'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'IQ', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/iraq-address-areas.csv',
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
            'AN' => 'AN',
            'AR' => 'AR',
            'BA' => 'BA',
            'BB' => 'BB',
            'BG' => 'BG',
            'DA' => 'DA',
            'DI' => 'DI',
            'DQ' => 'DQ',
            'HL' => 'HL',
            'KA' => 'KA',
            'KI' => 'KI',
            'MA' => 'MA',
            'MU' => 'MU',
            'NA' => 'NA',
            'NI' => 'NI',
            'QA' => 'QA',
            'SD' => 'SD',
            'SU' => 'SU',
            'WA' => 'WA',
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
            ['name' => 'Al Anbar', 'code' => 'AN'],
            ['name' => 'Erbil', 'code' => 'AR'],
            ['name' => 'Basra', 'code' => 'BA'],
            ['name' => 'Babylon', 'code' => 'BB'],
            ['name' => 'Baghdad', 'code' => 'BG'],
            ['name' => 'Dohuk', 'code' => 'DA'],
            ['name' => 'Diyala', 'code' => 'DI'],
            ['name' => 'Dhi Qar', 'code' => 'DQ'],
            ['name' => 'Halabja', 'code' => 'HL'],
            ['name' => 'Karbala', 'code' => 'KA'],
            ['name' => 'Kirkuk', 'code' => 'KI'],
            ['name' => 'Maysan', 'code' => 'MA'],
            ['name' => 'Al Muthanna', 'code' => 'MU'],
            ['name' => 'Najaf', 'code' => 'NA'],
            ['name' => 'Nineveh', 'code' => 'NI'],
            ['name' => 'Al-Qadisiyyah', 'code' => 'QA'],
            ['name' => 'Saladin', 'code' => 'SD'],
            ['name' => 'Sulaymaniyah', 'code' => 'SU'],
            ['name' => 'Wasit', 'code' => 'WA'],
        ];
    }
}
