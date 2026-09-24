<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Guam;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Contracts\CountryPostalCodeNormalizer;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;
use AIArmada\Addressing\Support\UsZipCodeKeys;

class GuamGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider, CountryPostalCodeNormalizer
{
    public const string AREA_SOURCE = 'aiarmada_addressing_guam_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.guam';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'GU';
    }

    /** @return list<string> */
    public function postalCodeLookupKeys(string $code): array
    {
        return UsZipCodeKeys::expand($code);
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
                        key: 'village',
                        label: 'Village',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['village'],
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
                'village' => ['village'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'GU', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/guam-address-areas.csv',
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
            '01' => '01',
            '02' => '02',
            '03' => '03',
            '04' => '04',
            '05' => '05',
            '06' => '06',
            '12' => '12',
            '08' => '08',
            '10' => '10',
            '09' => '09',
            '11' => '11',
            '13' => '13',
            '14' => '14',
            '15' => '15',
            '16' => '16',
            '17' => '17',
            '07' => '07',
            '18' => '18',
            '19' => '19',
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
            ['name' => 'Agana Heights', 'code' => '01'],
            ['name' => 'Asan-Maina', 'code' => '02'],
            ['name' => 'Barrigada', 'code' => '03'],
            ['name' => 'Chalan Pago-Ordot', 'code' => '04'],
            ['name' => 'Dededo', 'code' => '05'],
            ['name' => 'Hågat', 'code' => '06'],
            ['name' => 'Hagåtña', 'code' => '12'],
            ['name' => 'Inalåhan (Inarajan)', 'code' => '08'],
            ['name' => 'Mangilao', 'code' => '10'],
            ['name' => 'Malesso\' (Merizo)', 'code' => '09'],
            ['name' => 'Mongmong-Toto-Maite', 'code' => '11'],
            ['name' => 'Piti', 'code' => '13'],
            ['name' => 'Sånta Rita-Sumai (Santa Rita)', 'code' => '14'],
            ['name' => 'Sinajana', 'code' => '15'],
            ['name' => 'Talo\'fo\'fo (Talofofo)', 'code' => '16'],
            ['name' => 'Tamuning', 'code' => '17'],
            ['name' => 'Humåtak (Umatac)', 'code' => '07'],
            ['name' => 'Yigo', 'code' => '18'],
            ['name' => 'Yona', 'code' => '19'],
        ];
    }
}
