<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Switzerland;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class SwitzerlandGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_switzerland_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.switzerland';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'CH';
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
                static fn (string $role): array => ['role' => $role, 'country_code' => 'CH', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/switzerland-address-areas.csv',
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
            'AG' => 'AG',
            'AR' => 'AR',
            'AI' => 'AI',
            'BL' => 'BL',
            'BS' => 'BS',
            'BE' => 'BE',
            'FR' => 'FR',
            'GE' => 'GE',
            'GL' => 'GL',
            'GR' => 'GR',
            'JU' => 'JU',
            'LU' => 'LU',
            'NE' => 'NE',
            'NW' => 'NW',
            'OW' => 'OW',
            'SH' => 'SH',
            'SZ' => 'SZ',
            'SO' => 'SO',
            'SG' => 'SG',
            'TG' => 'TG',
            'TI' => 'TI',
            'UR' => 'UR',
            'VS' => 'VS',
            'VD' => 'VD',
            'ZG' => 'ZG',
            'ZH' => 'ZH',
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
            ['name' => 'Aargau', 'code' => 'AG'],
            ['name' => 'Appenzell Ausserrhoden', 'code' => 'AR'],
            ['name' => 'Appenzell Innerrhoden', 'code' => 'AI'],
            ['name' => 'Basel-Land', 'code' => 'BL'],
            ['name' => 'Basel-Stadt', 'code' => 'BS'],
            ['name' => 'Bern', 'code' => 'BE'],
            ['name' => 'Fribourg', 'code' => 'FR'],
            ['name' => 'Geneva', 'code' => 'GE'],
            ['name' => 'Glarus', 'code' => 'GL'],
            ['name' => 'Graubünden', 'code' => 'GR'],
            ['name' => 'Jura', 'code' => 'JU'],
            ['name' => 'Lucerne', 'code' => 'LU'],
            ['name' => 'Neuchâtel', 'code' => 'NE'],
            ['name' => 'Nidwalden', 'code' => 'NW'],
            ['name' => 'Obwalden', 'code' => 'OW'],
            ['name' => 'Schaffhausen', 'code' => 'SH'],
            ['name' => 'Schwyz', 'code' => 'SZ'],
            ['name' => 'Solothurn', 'code' => 'SO'],
            ['name' => 'St. Gallen', 'code' => 'SG'],
            ['name' => 'Thurgau', 'code' => 'TG'],
            ['name' => 'Ticino', 'code' => 'TI'],
            ['name' => 'Uri', 'code' => 'UR'],
            ['name' => 'Valais', 'code' => 'VS'],
            ['name' => 'Vaud', 'code' => 'VD'],
            ['name' => 'Zug', 'code' => 'ZG'],
            ['name' => 'Zürich', 'code' => 'ZH'],
        ];
    }
}
