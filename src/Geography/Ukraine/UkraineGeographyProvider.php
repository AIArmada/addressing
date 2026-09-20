<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Ukraine;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class UkraineGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_ukraine_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.ukraine';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'UA';
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
                        key: 'oblast',
                        label: 'Oblast / City / Republic',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['oblast', 'city', 'republic'],
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
                'oblast' => ['oblast'],
                'city' => ['oblast'],
                'republic' => ['oblast'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'UA', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/ukraine-address-areas.csv',
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
            '05' => '05',
            '07' => '07',
            '09' => '09',
            '12' => '12',
            '14' => '14',
            '18' => '18',
            '21' => '21',
            '23' => '23',
            '26' => '26',
            '30' => '30',
            '32' => '32',
            '35' => '35',
            '40' => '40',
            '43' => '43',
            '46' => '46',
            '48' => '48',
            '51' => '51',
            '53' => '53',
            '56' => '56',
            '59' => '59',
            '61' => '61',
            '63' => '63',
            '65' => '65',
            '68' => '68',
            '71' => '71',
            '74' => '74',
            '77' => '77',
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
            ['name' => 'Vinnytska', 'code' => '05'],
            ['name' => 'Volynska', 'code' => '07'],
            ['name' => 'Luhanska', 'code' => '09'],
            ['name' => 'Dnipropetrovska', 'code' => '12'],
            ['name' => 'Donetska', 'code' => '14'],
            ['name' => 'Zhytomyrska', 'code' => '18'],
            ['name' => 'Zakarpatska', 'code' => '21'],
            ['name' => 'Zaporizka', 'code' => '23'],
            ['name' => 'Ivano-Frankivska', 'code' => '26'],
            ['name' => 'Kyiv', 'code' => '30'],
            ['name' => 'Kyivska', 'code' => '32'],
            ['name' => 'Kirovohradska', 'code' => '35'],
            ['name' => 'Sevastopol', 'code' => '40'],
            ['name' => 'Autonomous Republic of Crimea', 'code' => '43'],
            ['name' => 'Lvivska', 'code' => '46'],
            ['name' => 'Mykolaivska', 'code' => '48'],
            ['name' => 'Odeska', 'code' => '51'],
            ['name' => 'Poltavska', 'code' => '53'],
            ['name' => 'Rivnenska', 'code' => '56'],
            ['name' => 'Sumska', 'code' => '59'],
            ['name' => 'Ternopilska', 'code' => '61'],
            ['name' => 'Kharkivska', 'code' => '63'],
            ['name' => 'Khersonska', 'code' => '65'],
            ['name' => 'Khmelnytska', 'code' => '68'],
            ['name' => 'Cherkaska', 'code' => '71'],
            ['name' => 'Chernihivska', 'code' => '74'],
            ['name' => 'Chernivetska', 'code' => '77'],
        ];
    }
}
