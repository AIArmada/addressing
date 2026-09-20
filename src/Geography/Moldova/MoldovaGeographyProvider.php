<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Moldova;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class MoldovaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_moldova_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.moldova';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'MD';
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
                        label: 'District / City / Autonomous Territorial Unit / Territorial Unit',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['district', 'city', 'autonomous_territorial_unit', 'territorial_unit'],
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
                'city' => ['city'],
                'autonomous_territorial_unit' => ['autonomous_territorial_unit'],
                'territorial_unit' => ['territorial_unit'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'MD', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/moldova-address-areas.csv',
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
            'BA' => 'BA',
            'BS' => 'BS',
            'BD' => 'BD',
            'BR' => 'BR',
            'CA' => 'CA',
            'CL' => 'CL',
            'CT' => 'CT',
            'CS' => 'CS',
            'CU' => 'CU',
            'CM' => 'CM',
            'CR' => 'CR',
            'DO' => 'DO',
            'DR' => 'DR',
            'DU' => 'DU',
            'ED' => 'ED',
            'FA' => 'FA',
            'FL' => 'FL',
            'GA' => 'GA',
            'GL' => 'GL',
            'HI' => 'HI',
            'IA' => 'IA',
            'LE' => 'LE',
            'NI' => 'NI',
            'OC' => 'OC',
            'OR' => 'OR',
            'RE' => 'RE',
            'RI' => 'RI',
            'SI' => 'SI',
            'SD' => 'SD',
            'SO' => 'SO',
            'SV' => 'SV',
            'ST' => 'ST',
            'TA' => 'TA',
            'TE' => 'TE',
            'SN' => 'SN',
            'UN' => 'UN',
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
            ['name' => 'Anenii Noi', 'code' => 'AN'],
            ['name' => 'Bălți', 'code' => 'BA'],
            ['name' => 'Basarabeasca', 'code' => 'BS'],
            ['name' => 'Bender', 'code' => 'BD'],
            ['name' => 'Briceni', 'code' => 'BR'],
            ['name' => 'Cahul', 'code' => 'CA'],
            ['name' => 'Călărași', 'code' => 'CL'],
            ['name' => 'Cantemir', 'code' => 'CT'],
            ['name' => 'Căușeni', 'code' => 'CS'],
            ['name' => 'Chișinău', 'code' => 'CU'],
            ['name' => 'Cimișlia', 'code' => 'CM'],
            ['name' => 'Criuleni', 'code' => 'CR'],
            ['name' => 'Dondușeni', 'code' => 'DO'],
            ['name' => 'Drochia', 'code' => 'DR'],
            ['name' => 'Dubăsari', 'code' => 'DU'],
            ['name' => 'Edineț', 'code' => 'ED'],
            ['name' => 'Fălești', 'code' => 'FA'],
            ['name' => 'Florești', 'code' => 'FL'],
            ['name' => 'Gagauzia', 'code' => 'GA'],
            ['name' => 'Glodeni', 'code' => 'GL'],
            ['name' => 'Hîncești', 'code' => 'HI'],
            ['name' => 'Ialoveni', 'code' => 'IA'],
            ['name' => 'Leova', 'code' => 'LE'],
            ['name' => 'Nisporeni', 'code' => 'NI'],
            ['name' => 'Ocnița', 'code' => 'OC'],
            ['name' => 'Orhei', 'code' => 'OR'],
            ['name' => 'Rezina', 'code' => 'RE'],
            ['name' => 'Rîșcani', 'code' => 'RI'],
            ['name' => 'Sîngerei', 'code' => 'SI'],
            ['name' => 'Șoldănești', 'code' => 'SD'],
            ['name' => 'Soroca', 'code' => 'SO'],
            ['name' => 'Ștefan Vodă', 'code' => 'SV'],
            ['name' => 'Strășeni', 'code' => 'ST'],
            ['name' => 'Taraclia', 'code' => 'TA'],
            ['name' => 'Telenești', 'code' => 'TE'],
            ['name' => 'Transnistria', 'code' => 'SN'],
            ['name' => 'Ungheni', 'code' => 'UN'],
        ];
    }
}
