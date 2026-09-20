<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Bhutan;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class BhutanGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_bhutan_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.bhutan';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'BT';
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
                        key: 'district',
                        label: 'District',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['district'],
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
                'district' => ['district'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'BT', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/bhutan-address-areas.csv',
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
            '33' => '33',
            '12' => '12',
            '22' => '22',
            'GA' => 'GA',
            '13' => '13',
            '44' => '44',
            '42' => '42',
            '11' => '11',
            '43' => '43',
            '23' => '23',
            '45' => '45',
            '14' => '14',
            '31' => '31',
            '15' => '15',
            'TY' => 'TY',
            '41' => '41',
            '32' => '32',
            '21' => '21',
            '24' => '24',
            '34' => '34',
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
            ['name' => 'Bumthang', 'code' => '33'],
            ['name' => 'Chukha', 'code' => '12'],
            ['name' => 'Dagana', 'code' => '22'],
            ['name' => 'Gasa', 'code' => 'GA'],
            ['name' => 'Haa', 'code' => '13'],
            ['name' => 'Lhuntse', 'code' => '44'],
            ['name' => 'Mongar', 'code' => '42'],
            ['name' => 'Paro', 'code' => '11'],
            ['name' => 'Pemagatshel', 'code' => '43'],
            ['name' => 'Punakha', 'code' => '23'],
            ['name' => 'Samdrup Jongkhar', 'code' => '45'],
            ['name' => 'Samtse', 'code' => '14'],
            ['name' => 'Sarpang', 'code' => '31'],
            ['name' => 'Thimphu', 'code' => '15'],
            ['name' => 'Trashi Yangtse', 'code' => 'TY'],
            ['name' => 'Trashigang', 'code' => '41'],
            ['name' => 'Trongsa', 'code' => '32'],
            ['name' => 'Tsirang', 'code' => '21'],
            ['name' => 'Wangdue Phodrang', 'code' => '24'],
            ['name' => 'Zhemgang', 'code' => '34'],
        ];
    }
}
