<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\TrinidadAndTobago;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class TrinidadAndTobagoGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_trinidad_and_tobago_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.trinidad_and_tobago';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'TT';
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

        // Eastern/Western Tobago (ETO/WTO) are stale splits; Tobago is a
        // single ward. Delete stragglers seeded before that fix.
        $stateClass::query()
            ->where('country_id', $country->id)
            ->whereIn('code', ['ETO', 'WTO'])
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
                        label: 'Region / Borough / City / Ward',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['region', 'borough', 'city', 'ward'],
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
                'borough' => ['borough'],
                'city' => ['city'],
                'ward' => ['ward'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'TT', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/trinidad-and-tobago-address-areas.csv',
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
            'ARI' => 'ARI',
            'CHA' => 'CHA',
            'CTT' => 'CTT',
            'DMN' => 'DMN',
            'MRC' => 'MRC',
            'PED' => 'PED',
            'PTF' => 'PTF',
            'POS' => 'POS',
            'PRT' => 'PRT',
            'SFO' => 'SFO',
            'SJL' => 'SJL',
            'SGE' => 'SGE',
            'SIP' => 'SIP',
            'TOB' => 'TOB',
            'TUP' => 'TUP',
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
            ['name' => 'Arima', 'code' => 'ARI'],
            ['name' => 'Chaguanas', 'code' => 'CHA'],
            ['name' => 'Couva-Tabaquite-Talparo', 'code' => 'CTT'],
            ['name' => 'Diego Martin', 'code' => 'DMN'],
            ['name' => 'Mayaro-Rio Claro', 'code' => 'MRC'],
            ['name' => 'Penal-Debe', 'code' => 'PED'],
            ['name' => 'Point Fortin', 'code' => 'PTF'],
            ['name' => 'Port of Spain', 'code' => 'POS'],
            ['name' => 'Princes Town', 'code' => 'PRT'],
            ['name' => 'San Fernando', 'code' => 'SFO'],
            ['name' => 'San Juan-Laventille', 'code' => 'SJL'],
            ['name' => 'Sangre Grande', 'code' => 'SGE'],
            ['name' => 'Siparia', 'code' => 'SIP'],
            ['name' => 'Tobago', 'code' => 'TOB'],
            ['name' => 'Tunapuna-Piarco', 'code' => 'TUP'],
        ];
    }
}
