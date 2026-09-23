<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Mongolia;

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

class MongoliaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_mongolia_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.mongolia';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'MN';
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

        // Ulaanbaatar follows ISO MN-1 (single digit). Delete stragglers
        // seeded with the zero-padded code so reseeds converge.
        $stateClass::query()
            ->where('country_id', $country->id)
            ->where('code', '001')
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
                        key: 'province',
                        label: 'Province / Capital City',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['province', 'capital_city'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'district',
                        label: 'Sum / Düüreg',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['sum', 'duureg'],
                        areaLevels: [2],
                        parentKey: 'province',
                        assignmentRole: 'district',
                    ),
                ],
            ),
        ];
    }

    /** @return array<string, string> */
    public function areaTypeLabels(): array
    {
        // Mongolian administrative terms (sums in the aimags, düüregs in Ulaanbaatar).
        return [
            'province' => 'Aimag',
            'sum' => 'Sum',
            'duureg' => 'Düüreg',
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
                'province' => ['province'],
                'capital_city' => ['capital_city'],
                'sum' => ['district'],
                'duureg' => ['district'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'MN', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/mongolia-address-areas.csv',
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
            '073' => '073',
            '071' => '071',
            '069' => '069',
            '067' => '067',
            '037' => '037',
            '061' => '061',
            '063' => '063',
            '059' => '059',
            '065' => '065',
            '064' => '064',
            '039' => '039',
            '043' => '043',
            '041' => '041',
            '053' => '053',
            '035' => '035',
            '055' => '055',
            '049' => '049',
            '051' => '051',
            '047' => '047',
            '1' => '1',
            '046' => '046',
            '057' => '057',
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
            ['name' => 'Arkhangai', 'code' => '073'],
            ['name' => 'Bayan-Ölgii', 'code' => '071'],
            ['name' => 'Bayankhongor', 'code' => '069'],
            ['name' => 'Bulgan', 'code' => '067'],
            ['name' => 'Darkhan-Uul', 'code' => '037'],
            ['name' => 'Dornod', 'code' => '061'],
            ['name' => 'Dornogovi', 'code' => '063'],
            ['name' => 'Dundgovi', 'code' => '059'],
            ['name' => 'Govi-Altai', 'code' => '065'],
            ['name' => 'Govisümber', 'code' => '064'],
            ['name' => 'Khentii', 'code' => '039'],
            ['name' => 'Khovd', 'code' => '043'],
            ['name' => 'Khövsgöl', 'code' => '041'],
            ['name' => 'Ömnögovi', 'code' => '053'],
            ['name' => 'Orkhon', 'code' => '035'],
            ['name' => 'Övörkhangai', 'code' => '055'],
            ['name' => 'Selenge', 'code' => '049'],
            ['name' => 'Sükhbaatar', 'code' => '051'],
            ['name' => 'Töv', 'code' => '047'],
            ['name' => 'Ulaanbaatar', 'code' => '1'],
            ['name' => 'Uvs', 'code' => '046'],
            ['name' => 'Zavkhan', 'code' => '057'],
        ];
    }
}
