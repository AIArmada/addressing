<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Yemen;

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

class YemenGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_yemen_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.yemen';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'YE';
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
                        label: 'Governorate / Municipality',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['governorate', 'municipality'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'district',
                        label: 'District',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['district'],
                        areaLevels: [2],
                        parentKey: 'governorate',
                        assignmentRole: 'district',
                    ),
                ],
            ),
        ];
    }

    /** @return array<string, string> */
    public function areaTypeLabels(): array
    {
        // Arabic administrative terms.
        return [
            'governorate' => 'Muhafaza',
            'municipality' => 'Municipality',
            'district' => 'District',
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
                'governorate' => ['governorate'],
                'municipality' => ['municipality'],
                'district' => ['district'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'YE', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/yemen-address-areas.csv',
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
            'AB' => 'AB',
            'DA' => 'DA',
            'AD' => 'AD',
            'BA' => 'BA',
            'HU' => 'HU',
            'JA' => 'JA',
            'MR' => 'MR',
            'MW' => 'MW',
            'SA' => 'SA',
            'AM' => 'AM',
            'DH' => 'DH',
            'HD' => 'HD',
            'HJ' => 'HJ',
            'IB' => 'IB',
            'LA' => 'LA',
            'MA' => 'MA',
            'RA' => 'RA',
            'SD' => 'SD',
            'SN' => 'SN',
            'SH' => 'SH',
            'SU' => 'SU',
            'TA' => 'TA',
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
            ['name' => 'Abyan', 'code' => 'AB'],
            ['name' => 'Ad Dali\'', 'code' => 'DA'],
            ['name' => 'Adan', 'code' => 'AD'],
            ['name' => 'Al Bayda\'', 'code' => 'BA'],
            ['name' => 'Al Hudaydah', 'code' => 'HU'],
            ['name' => 'Al Jawf', 'code' => 'JA'],
            ['name' => 'Al Mahrah', 'code' => 'MR'],
            ['name' => 'Al Mahwit', 'code' => 'MW'],
            ['name' => 'Amanat Al Asimah', 'code' => 'SA'],
            ['name' => 'Amran', 'code' => 'AM'],
            ['name' => 'Dhamar', 'code' => 'DH'],
            ['name' => 'Hadhramaut', 'code' => 'HD'],
            ['name' => 'Hajjah', 'code' => 'HJ'],
            ['name' => 'Ibb', 'code' => 'IB'],
            ['name' => 'Lahij', 'code' => 'LA'],
            ['name' => 'Ma\'rib', 'code' => 'MA'],
            ['name' => 'Raymah', 'code' => 'RA'],
            ['name' => 'Saada', 'code' => 'SD'],
            ['name' => 'Sana\'a', 'code' => 'SN'],
            ['name' => 'Shabwah', 'code' => 'SH'],
            ['name' => 'Socotra', 'code' => 'SU'],
            ['name' => 'Ta\'izz', 'code' => 'TA'],
        ];
    }
}
