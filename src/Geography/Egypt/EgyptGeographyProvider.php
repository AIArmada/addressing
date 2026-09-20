<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Egypt;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class EgyptGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_egypt_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.egypt';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'EG';
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
                static fn (string $role): array => ['role' => $role, 'country_code' => 'EG', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/egypt-address-areas.csv',
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
            'ALX' => 'ALX',
            'ASN' => 'ASN',
            'AST' => 'AST',
            'BA' => 'BA',
            'BH' => 'BH',
            'BNS' => 'BNS',
            'C' => 'C',
            'DK' => 'DK',
            'DT' => 'DT',
            'FYM' => 'FYM',
            'GH' => 'GH',
            'GZ' => 'GZ',
            'IS' => 'IS',
            'JS' => 'JS',
            'KB' => 'KB',
            'KFS' => 'KFS',
            'KN' => 'KN',
            'LX' => 'LX',
            'MN' => 'MN',
            'MNF' => 'MNF',
            'MT' => 'MT',
            'PTS' => 'PTS',
            'SHG' => 'SHG',
            'SHR' => 'SHR',
            'SIN' => 'SIN',
            'SUZ' => 'SUZ',
            'WAD' => 'WAD',
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
            ['name' => 'Alexandria', 'code' => 'ALX'],
            ['name' => 'Aswan', 'code' => 'ASN'],
            ['name' => 'Asyut', 'code' => 'AST'],
            ['name' => 'Red Sea', 'code' => 'BA'],
            ['name' => 'Beheira', 'code' => 'BH'],
            ['name' => 'Beni Suef', 'code' => 'BNS'],
            ['name' => 'Cairo', 'code' => 'C'],
            ['name' => 'Dakahlia', 'code' => 'DK'],
            ['name' => 'Damietta', 'code' => 'DT'],
            ['name' => 'Faiyum', 'code' => 'FYM'],
            ['name' => 'Gharbia', 'code' => 'GH'],
            ['name' => 'Giza', 'code' => 'GZ'],
            ['name' => 'Ismailia', 'code' => 'IS'],
            ['name' => 'South Sinai', 'code' => 'JS'],
            ['name' => 'Qalyubia', 'code' => 'KB'],
            ['name' => 'Kafr El-Sheikh', 'code' => 'KFS'],
            ['name' => 'Qena', 'code' => 'KN'],
            ['name' => 'Luxor', 'code' => 'LX'],
            ['name' => 'Minya', 'code' => 'MN'],
            ['name' => 'Monufia', 'code' => 'MNF'],
            ['name' => 'Matrouh', 'code' => 'MT'],
            ['name' => 'Port Said', 'code' => 'PTS'],
            ['name' => 'Sohag', 'code' => 'SHG'],
            ['name' => 'Sharqia', 'code' => 'SHR'],
            ['name' => 'North Sinai', 'code' => 'SIN'],
            ['name' => 'Suez', 'code' => 'SUZ'],
            ['name' => 'New Valley', 'code' => 'WAD'],
        ];
    }
}
