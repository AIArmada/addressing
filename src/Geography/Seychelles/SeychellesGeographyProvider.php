<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Seychelles;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class SeychellesGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_seychelles_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.seychelles';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'SC';
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
                static fn (string $role): array => ['role' => $role, 'country_code' => 'SC', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/seychelles-address-areas.csv',
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
            '02' => '02',
            '03' => '03',
            '05' => '05',
            '01' => '01',
            '04' => '04',
            '06' => '06',
            '07' => '07',
            '08' => '08',
            '09' => '09',
            '10' => '10',
            '11' => '11',
            '12' => '12',
            '13' => '13',
            '14' => '14',
            '26' => '26',
            '27' => '27',
            '15' => '15',
            '16' => '16',
            '24' => '24',
            '17' => '17',
            '18' => '18',
            '19' => '19',
            '20' => '20',
            '21' => '21',
            '25' => '25',
            '22' => '22',
            '23' => '23',
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
            ['name' => 'Anse Boileau', 'code' => '02'],
            ['name' => 'Anse Etoile', 'code' => '03'],
            ['name' => 'Anse Royale', 'code' => '05'],
            ['name' => 'Anse-aux-Pins', 'code' => '01'],
            ['name' => 'Au Cap', 'code' => '04'],
            ['name' => 'Baie Lazare', 'code' => '06'],
            ['name' => 'Baie Sainte Anne', 'code' => '07'],
            ['name' => 'Beau Vallon', 'code' => '08'],
            ['name' => 'Bel Air', 'code' => '09'],
            ['name' => 'Bel Ombre', 'code' => '10'],
            ['name' => 'Cascade', 'code' => '11'],
            ['name' => 'Glacis', 'code' => '12'],
            ['name' => 'Grand\'Anse Mahé', 'code' => '13'],
            ['name' => 'Grand\'Anse Praslin', 'code' => '14'],
            ['name' => 'Ile Perseverance I', 'code' => '26'],
            ['name' => 'Ile Perseverance II', 'code' => '27'],
            ['name' => 'La Digue', 'code' => '15'],
            ['name' => 'La Rivière Anglaise', 'code' => '16'],
            ['name' => 'Les Mamelles', 'code' => '24'],
            ['name' => 'Mont Buxton', 'code' => '17'],
            ['name' => 'Mont Fleuri', 'code' => '18'],
            ['name' => 'Plaisance', 'code' => '19'],
            ['name' => 'Pointe La Rue', 'code' => '20'],
            ['name' => 'Port Glaud', 'code' => '21'],
            ['name' => 'Roche Caiman', 'code' => '25'],
            ['name' => 'Saint Louis', 'code' => '22'],
            ['name' => 'Takamaka', 'code' => '23'],
        ];
    }
}
