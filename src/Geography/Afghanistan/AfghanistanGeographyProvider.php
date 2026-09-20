<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Afghanistan;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class AfghanistanGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_afghanistan_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.afghanistan';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'AF';
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
                        key: 'province',
                        label: 'Province',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['province'],
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
                'province' => ['province'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'AF', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/afghanistan-address-areas.csv',
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
            'BAL' => 'BAL',
            'BAM' => 'BAM',
            'BDG' => 'BDG',
            'BDS' => 'BDS',
            'BGL' => 'BGL',
            'DAY' => 'DAY',
            'FRA' => 'FRA',
            'FYB' => 'FYB',
            'GHA' => 'GHA',
            'GHO' => 'GHO',
            'HEL' => 'HEL',
            'HER' => 'HER',
            'JOW' => 'JOW',
            'KAB' => 'KAB',
            'KAN' => 'KAN',
            'KAP' => 'KAP',
            'KDZ' => 'KDZ',
            'KHO' => 'KHO',
            'KNR' => 'KNR',
            'LAG' => 'LAG',
            'LOG' => 'LOG',
            'NAN' => 'NAN',
            'NIM' => 'NIM',
            'NUR' => 'NUR',
            'PAN' => 'PAN',
            'PAR' => 'PAR',
            'PIA' => 'PIA',
            'PKA' => 'PKA',
            'SAM' => 'SAM',
            'SAR' => 'SAR',
            'TAK' => 'TAK',
            'URU' => 'URU',
            'WAR' => 'WAR',
            'ZAB' => 'ZAB',
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
            ['name' => 'Balkh', 'code' => 'BAL'],
            ['name' => 'Bamyan', 'code' => 'BAM'],
            ['name' => 'Badghis', 'code' => 'BDG'],
            ['name' => 'Badakhshan', 'code' => 'BDS'],
            ['name' => 'Baghlan', 'code' => 'BGL'],
            ['name' => 'Daykundi', 'code' => 'DAY'],
            ['name' => 'Farah', 'code' => 'FRA'],
            ['name' => 'Faryab', 'code' => 'FYB'],
            ['name' => 'Ghazni', 'code' => 'GHA'],
            ['name' => 'Ghor', 'code' => 'GHO'],
            ['name' => 'Helmand', 'code' => 'HEL'],
            ['name' => 'Herat', 'code' => 'HER'],
            ['name' => 'Jowzjan', 'code' => 'JOW'],
            ['name' => 'Kabul', 'code' => 'KAB'],
            ['name' => 'Kandahar', 'code' => 'KAN'],
            ['name' => 'Kapisa', 'code' => 'KAP'],
            ['name' => 'Kunduz', 'code' => 'KDZ'],
            ['name' => 'Khost', 'code' => 'KHO'],
            ['name' => 'Kunar', 'code' => 'KNR'],
            ['name' => 'Laghman', 'code' => 'LAG'],
            ['name' => 'Logar', 'code' => 'LOG'],
            ['name' => 'Nangarhar', 'code' => 'NAN'],
            ['name' => 'Nimruz', 'code' => 'NIM'],
            ['name' => 'Nuristan', 'code' => 'NUR'],
            ['name' => 'Panjshir', 'code' => 'PAN'],
            ['name' => 'Parwan', 'code' => 'PAR'],
            ['name' => 'Paktia', 'code' => 'PIA'],
            ['name' => 'Paktika', 'code' => 'PKA'],
            ['name' => 'Samangan', 'code' => 'SAM'],
            ['name' => 'Sar-e Pol', 'code' => 'SAR'],
            ['name' => 'Takhar', 'code' => 'TAK'],
            ['name' => 'Urozgan', 'code' => 'URU'],
            ['name' => 'Wardak', 'code' => 'WAR'],
            ['name' => 'Zabul', 'code' => 'ZAB'],
        ];
    }
}
