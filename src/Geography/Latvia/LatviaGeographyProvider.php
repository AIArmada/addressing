<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Latvia;

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

class LatviaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_latvia_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.latvia';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'LV';
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

        // Varakļāni Municipality merged into Madona on 1 July 2025.
        // Delete stragglers seeded before that fix.
        $stateClass::query()
            ->where('country_id', $country->id)
            ->where('code', '102')
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
                        key: 'municipality',
                        label: 'Municipality / State City',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['municipality', 'state_city'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'parish',
                        label: 'Parish / Town / City',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['parish', 'town', 'city'],
                        areaLevels: [2],
                        parentKey: 'municipality',
                        assignmentRole: 'parish',
                    ),
                ],
            ),
        ];
    }

    /** @return array<string, string> */
    public function areaTypeLabels(): array
    {
        // Latvian administrative terms.
        return [
            'municipality' => 'Novads',
            'state_city' => 'Valstspilsēta',
            'parish' => 'Pagasts',
            'town' => 'Pilsēta',
            'city' => 'Pilsēta',
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
                'municipality' => ['municipality'],
                'state_city' => ['state_city'],
                'parish' => ['parish'],
                'town' => ['parish'],
                'city' => ['parish'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'LV', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/latvia-address-areas.csv',
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
            '011' => '011',
            '002' => '002',
            '007' => '007',
            '111' => '111',
            '015' => '015',
            '016' => '016',
            '022' => '022',
            'DGV' => 'DGV',
            '112' => '112',
            '026' => '026',
            '033' => '033',
            '042' => '042',
            '041' => '041',
            'JEL' => 'JEL',
            'JUR' => 'JUR',
            '052' => '052',
            '047' => '047',
            '050' => '050',
            'LPX' => 'LPX',
            '054' => '054',
            '056' => '056',
            '058' => '058',
            '059' => '059',
            '062' => '062',
            '067' => '067',
            '068' => '068',
            '073' => '073',
            'REZ' => 'REZ',
            '077' => '077',
            'RIX' => 'RIX',
            '080' => '080',
            '087' => '087',
            '088' => '088',
            '089' => '089',
            '091' => '091',
            '094' => '094',
            '097' => '097',
            '099' => '099',
            '101' => '101',
            '113' => '113',
            'VEN' => 'VEN',
            '106' => '106',
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
            ['name' => 'Ādaži', 'code' => '011'],
            ['name' => 'Aizkraukle', 'code' => '002'],
            ['name' => 'Alūksne', 'code' => '007'],
            ['name' => 'Augšdaugava', 'code' => '111'],
            ['name' => 'Balvi', 'code' => '015'],
            ['name' => 'Bauska', 'code' => '016'],
            ['name' => 'Cēsis', 'code' => '022'],
            ['name' => 'Daugavpils', 'code' => 'DGV'],
            ['name' => 'Dienvidkurzemes', 'code' => '112'],
            ['name' => 'Dobele', 'code' => '026'],
            ['name' => 'Gulbene', 'code' => '033'],
            ['name' => 'Jēkabpils', 'code' => '042'],
            ['name' => 'Jelgava', 'code' => '041'],
            ['name' => 'Jelgava', 'code' => 'JEL'],
            ['name' => 'Jūrmala', 'code' => 'JUR'],
            ['name' => 'Ķekava', 'code' => '052'],
            ['name' => 'Krāslava', 'code' => '047'],
            ['name' => 'Kuldīga', 'code' => '050'],
            ['name' => 'Liepāja', 'code' => 'LPX'],
            ['name' => 'Limbaži', 'code' => '054'],
            ['name' => 'Līvāni', 'code' => '056'],
            ['name' => 'Ludza', 'code' => '058'],
            ['name' => 'Madona', 'code' => '059'],
            ['name' => 'Mārupe', 'code' => '062'],
            ['name' => 'Ogre', 'code' => '067'],
            ['name' => 'Olaine', 'code' => '068'],
            ['name' => 'Preiļi', 'code' => '073'],
            ['name' => 'Rēzekne', 'code' => 'REZ'],
            ['name' => 'Rēzekne', 'code' => '077'],
            ['name' => 'Riga', 'code' => 'RIX'],
            ['name' => 'Ropaži', 'code' => '080'],
            ['name' => 'Salaspils', 'code' => '087'],
            ['name' => 'Saldus', 'code' => '088'],
            ['name' => 'Saulkrasti', 'code' => '089'],
            ['name' => 'Sigulda', 'code' => '091'],
            ['name' => 'Smiltene', 'code' => '094'],
            ['name' => 'Talsi', 'code' => '097'],
            ['name' => 'Tukums', 'code' => '099'],
            ['name' => 'Valka', 'code' => '101'],
            ['name' => 'Valmiera', 'code' => '113'],
            ['name' => 'Ventspils', 'code' => 'VEN'],
            ['name' => 'Ventspils', 'code' => '106'],
        ];
    }
}
