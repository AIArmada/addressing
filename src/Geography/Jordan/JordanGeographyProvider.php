<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Jordan;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class JordanGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_jordan_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.jordan';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'JO';
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
                        key: 'governorate',
                        label: 'Governorate',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['governorate'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'liwa',
                        label: 'Liwa',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['liwa'],
                        areaLevels: [2],
                        parentKey: 'governorate',
                        assignmentRole: 'liwa',
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
                'liwa' => ['liwa'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'JO', 'is_primary' => true],
                $areaRoles,
            );
        }

        return $roles;
    }

    /** @return array<string, list<array{name: string, name_type?: string, is_preferred?: bool}>> */
    public function areaNames(AddressCountry $country): array
    {
        return [
            'jo:liwa:ajloun:ajloun-qasabah' => [
                ['name' => 'Ajlun', 'name_type' => 'official'],
            ],
            'jo:liwa:ajloun:kufranjah' => [
                ['name' => 'Kufrinjah', 'name_type' => 'alternative'],
            ],
            'jo:liwa:amman:jizah' => [
                ['name' => 'Jizeh', 'name_type' => 'alternative'],
            ],
            'jo:liwa:amman:muaqqar' => [
                ['name' => 'Mowaqqar', 'name_type' => 'alternative'],
            ],
            'jo:liwa:amman:naour' => [
                ['name' => "Na'oor", 'name_type' => 'official'],
            ],
            'jo:liwa:amman:quaismeh' => [
                ['name' => 'Quwaysimah', 'name_type' => 'alternative'],
                ['name' => 'Al-Qwesmeh', 'name_type' => 'alternative'],
            ],
            'jo:liwa:amman:wadi-essier' => [
                ['name' => 'Wadi Al Seer', 'name_type' => 'alternative'],
            ],
            'jo:liwa:aqaba:quairah' => [
                ['name' => 'Al-Quwairah', 'name_type' => 'alternative'],
            ],
            'jo:liwa:balqa:ain-al-basha' => [
                ['name' => 'Ain Albasha', 'name_type' => 'official'],
            ],
            'jo:liwa:balqa:deir-alla' => [
                ['name' => 'Dair Alla', 'name_type' => 'official'],
            ],
            'jo:liwa:balqa:mahis-and-fuhais' => [
                ['name' => 'Fuhais&Mahes', 'name_type' => 'official'],
            ],
            'jo:liwa:jerash:jerash-qasabah' => [
                ['name' => 'Jarash', 'name_type' => 'official'],
            ],
            'jo:liwa:karak:ayy' => [
                ['name' => 'Aii', 'name_type' => 'alternative'],
            ],
            'jo:liwa:karak:faqo-e' => [
                ['name' => "Faqou'", 'name_type' => 'alternative'],
            ],
            'jo:liwa:ma-an:huseiniya' => [
                ['name' => 'Husseiniya', 'name_type' => 'alternative'],
            ],
            'jo:liwa:ma-an:shobak' => [
                ['name' => 'Shoubak', 'name_type' => 'alternative'],
            ],
            'jo:liwa:mafraq:rwaished' => [
                ['name' => 'Ruwayshid', 'name_type' => 'alternative'],
            ],
            'jo:liwa:tafilah:tafilah-qasabah' => [
                ['name' => 'Tafiela', 'name_type' => 'official'],
            ],
            'jo:liwa:zarqa:hashemiyah' => [
                ['name' => 'Hashimiyya', 'name_type' => 'alternative'],
            ],
        ];
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
            __DIR__ . '/../../../resources/geography/jordan-address-areas.csv',
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
            'AJ' => 'AJ',
            'AM' => 'AM',
            'AQ' => 'AQ',
            'AT' => 'AT',
            'AZ' => 'AZ',
            'BA' => 'BA',
            'IR' => 'IR',
            'JA' => 'JA',
            'KA' => 'KA',
            'MA' => 'MA',
            'MD' => 'MD',
            'MN' => 'MN',
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
            ['name' => 'Ajloun', 'code' => 'AJ'],
            ['name' => 'Amman', 'code' => 'AM'],
            ['name' => 'Aqaba', 'code' => 'AQ'],
            ['name' => 'Tafilah', 'code' => 'AT'],
            ['name' => 'Zarqa', 'code' => 'AZ'],
            ['name' => 'Balqa', 'code' => 'BA'],
            ['name' => 'Irbid', 'code' => 'IR'],
            ['name' => 'Jerash', 'code' => 'JA'],
            ['name' => 'Karak', 'code' => 'KA'],
            ['name' => 'Mafraq', 'code' => 'MA'],
            ['name' => 'Madaba', 'code' => 'MD'],
            ['name' => 'Ma\'an', 'code' => 'MN'],
        ];
    }
}
