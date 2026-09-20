<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Morocco;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class MoroccoGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_morocco_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.morocco';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'MA';
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
                        key: 'region',
                        label: 'Region',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['region'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'province',
                        label: 'Province / Prefecture',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['province', 'prefecture'],
                        areaLevels: [2],
                        parentKey: 'region',
                        assignmentRole: 'province',
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
                'province' => ['province'],
                'prefecture' => ['province'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'MA', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/morocco-address-areas.csv',
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
            '01' => '01',
            '02' => '02',
            '03' => '03',
            '04' => '04',
            '05' => '05',
            '06' => '06',
            '07' => '07',
            '08' => '08',
            '09' => '09',
            '10' => '10',
            '11' => '11',
            '12' => '12',
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
            ['name' => 'Tanger-Tétouan-Al Hoceïma', 'code' => '01'],
            ['name' => 'L\'Oriental', 'code' => '02'],
            ['name' => 'Fès-Meknès', 'code' => '03'],
            ['name' => 'Rabat-Salé-Kénitra', 'code' => '04'],
            ['name' => 'Béni Mellal-Khénifra', 'code' => '05'],
            ['name' => 'Casablanca-Settat', 'code' => '06'],
            ['name' => 'Marrakech-Safi', 'code' => '07'],
            ['name' => 'Drâa-Tafilalet', 'code' => '08'],
            ['name' => 'Souss-Massa', 'code' => '09'],
            ['name' => 'Guelmim-Oued Noun', 'code' => '10'],
            ['name' => 'Laâyoune-Sakia El Hamra', 'code' => '11'],
            ['name' => 'Dakhla-Oued Ed-Dahab', 'code' => '12'],
            ['name' => 'Agadir-Ida-Ou-Tanane', 'code' => 'AGD'],
            ['name' => 'Aousserd', 'code' => 'AOU'],
            ['name' => 'Assa-Zag', 'code' => 'ASZ'],
            ['name' => 'Azilal', 'code' => 'AZI'],
            ['name' => 'Béni Mellal', 'code' => 'BEM'],
            ['name' => 'Berkane', 'code' => 'BER'],
            ['name' => 'Benslimane', 'code' => 'BES'],
            ['name' => 'Boujdour', 'code' => 'BOD'],
            ['name' => 'Boulemane', 'code' => 'BOM'],
            ['name' => 'Berrechid', 'code' => 'BRR'],
            ['name' => 'Casablanca', 'code' => 'CAS'],
            ['name' => 'Chefchaouen', 'code' => 'CHE'],
            ['name' => 'Chichaoua', 'code' => 'CHI'],
            ['name' => 'Chtouka-Aït-Baha', 'code' => 'CHT'],
            ['name' => 'Driouch', 'code' => 'DRI'],
            ['name' => 'Errachidia', 'code' => 'ERR'],
            ['name' => 'Essaouira', 'code' => 'ESI'],
            ['name' => 'Es-Semara', 'code' => 'ESM'],
            ['name' => 'Fahs-Anjra', 'code' => 'FAH'],
            ['name' => 'Fès', 'code' => 'FES'],
            ['name' => 'Figuig', 'code' => 'FIG'],
            ['name' => 'Fquih Ben Salah', 'code' => 'FQH'],
            ['name' => 'Guelmim', 'code' => 'GUE'],
            ['name' => 'Guercif', 'code' => 'GUF'],
            ['name' => 'El Hajeb', 'code' => 'HAJ'],
            ['name' => 'Al Haouz', 'code' => 'HAO'],
            ['name' => 'Al Hoceïma', 'code' => 'HOC'],
            ['name' => 'Ifrane', 'code' => 'IFR'],
            ['name' => 'Inezgane-Aït-Melloul', 'code' => 'INE'],
            ['name' => 'El Jadida', 'code' => 'JDI'],
            ['name' => 'Jerada', 'code' => 'JRA'],
            ['name' => 'Kénitra', 'code' => 'KEN'],
            ['name' => 'El Kelâa des Sraghna', 'code' => 'KES'],
            ['name' => 'Khémisset', 'code' => 'KHE'],
            ['name' => 'Khénifra', 'code' => 'KHN'],
            ['name' => 'Khouribga', 'code' => 'KHO'],
            ['name' => 'Laâyoune', 'code' => 'LAA'],
            ['name' => 'Larache', 'code' => 'LAR'],
            ['name' => 'Marrakech', 'code' => 'MAR'],
            ['name' => 'M\'diq-Fnideq', 'code' => 'MDF'],
            ['name' => 'Médiouna', 'code' => 'MED'],
            ['name' => 'Meknès', 'code' => 'MEK'],
            ['name' => 'Midelt', 'code' => 'MID'],
            ['name' => 'Mohammedia', 'code' => 'MOH'],
            ['name' => 'Moulay Yacoub', 'code' => 'MOU'],
            ['name' => 'Nador', 'code' => 'NAD'],
            ['name' => 'Nouaceur', 'code' => 'NOU'],
            ['name' => 'Ouarzazate', 'code' => 'OUA'],
            ['name' => 'Oued Ed-Dahab', 'code' => 'OUD'],
            ['name' => 'Oujda-Angad', 'code' => 'OUJ'],
            ['name' => 'Ouezzane', 'code' => 'OUZ'],
            ['name' => 'Rabat', 'code' => 'RAB'],
            ['name' => 'Rehamna', 'code' => 'REH'],
            ['name' => 'Safi', 'code' => 'SAF'],
            ['name' => 'Salé', 'code' => 'SAL'],
            ['name' => 'Sefrou', 'code' => 'SEF'],
            ['name' => 'Settat', 'code' => 'SET'],
            ['name' => 'Sidi Bennour', 'code' => 'SIB'],
            ['name' => 'Sidi Ifni', 'code' => 'SIF'],
            ['name' => 'Sidi Kacem', 'code' => 'SIK'],
            ['name' => 'Sidi Slimane', 'code' => 'SIL'],
            ['name' => 'Skhirate-Témara', 'code' => 'SKH'],
            ['name' => 'Tarfaya', 'code' => 'TAF'],
            ['name' => 'Taourirt', 'code' => 'TAI'],
            ['name' => 'Taounate', 'code' => 'TAO'],
            ['name' => 'Taroudant', 'code' => 'TAR'],
            ['name' => 'Tata', 'code' => 'TAT'],
            ['name' => 'Taza', 'code' => 'TAZ'],
            ['name' => 'Tétouan', 'code' => 'TET'],
            ['name' => 'Tinghir', 'code' => 'TIN'],
            ['name' => 'Tiznit', 'code' => 'TIZ'],
            ['name' => 'Tanger-Assilah', 'code' => 'TNG'],
            ['name' => 'Tan-Tan', 'code' => 'TNT'],
            ['name' => 'Youssoufia', 'code' => 'YUS'],
            ['name' => 'Zagora', 'code' => 'ZAG'],
        ];
    }
}
