<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Hungary;

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

class HungaryGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_hungary_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.hungary';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'HU';
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
                        key: 'county',
                        label: 'County / City with County Rights / Capital City',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['county', 'city_with_county_rights', 'capital_city'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'district',
                        label: 'District',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['district'],
                        areaLevels: [2],
                        parentKey: 'county',
                        assignmentRole: 'district',
                    ),
                ],
            ),
        ];
    }

    /** @return array<string, string> */
    public function areaTypeLabels(): array
    {
        // Hungarian administrative terms.
        return [
            'county' => 'Vármegye',
            'city_with_county_rights' => 'Megyei Jogú Város',
            'capital_city' => 'Főváros',
            'district' => 'Járás',
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
                'county' => ['county'],
                'city_with_county_rights' => ['city_with_county_rights'],
                'capital_city' => ['capital_city'],
                'district' => ['district'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'HU', 'is_primary' => true],
                $areaRoles,
            );
        }

        return $roles;
    }

    /** @return array<string, list<array{name: string, name_type?: string, is_preferred?: bool}>> */
    public function areaNames(AddressCountry $country): array
    {
        return [
            'hu:county:csongrad-csanad-county' => [
                ['name' => 'Csongrád County', 'name_type' => 'historic'],
            ],
        ];
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
            __DIR__ . '/../../../resources/geography/hungary-address-areas.csv',
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
            'BK' => 'BK',
            'BA' => 'BA',
            'BE' => 'BE',
            'BC' => 'BC',
            'BZ' => 'BZ',
            'BU' => 'BU',
            'CS' => 'CS',
            'DE' => 'DE',
            'DU' => 'DU',
            'EG' => 'EG',
            'ER' => 'ER',
            'FE' => 'FE',
            'GY' => 'GY',
            'GS' => 'GS',
            'HB' => 'HB',
            'HE' => 'HE',
            'HV' => 'HV',
            'JN' => 'JN',
            'KV' => 'KV',
            'KM' => 'KM',
            'KE' => 'KE',
            'MI' => 'MI',
            'NK' => 'NK',
            'NO' => 'NO',
            'NY' => 'NY',
            'PS' => 'PS',
            'PE' => 'PE',
            'ST' => 'ST',
            'SO' => 'SO',
            'SN' => 'SN',
            'SZ' => 'SZ',
            'SD' => 'SD',
            'SF' => 'SF',
            'SS' => 'SS',
            'SK' => 'SK',
            'SH' => 'SH',
            'TB' => 'TB',
            'TO' => 'TO',
            'VA' => 'VA',
            'VM' => 'VM',
            'VE' => 'VE',
            'ZA' => 'ZA',
            'ZE' => 'ZE',
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
            ['name' => 'Bács-Kiskun', 'code' => 'BK'],
            ['name' => 'Baranya', 'code' => 'BA'],
            ['name' => 'Békés', 'code' => 'BE'],
            ['name' => 'Békéscsaba', 'code' => 'BC'],
            ['name' => 'Borsod-Abaúj-Zemplén', 'code' => 'BZ'],
            ['name' => 'Budapest', 'code' => 'BU'],
            ['name' => 'Csongrád-Csanád County', 'code' => 'CS'],
            ['name' => 'Debrecen', 'code' => 'DE'],
            ['name' => 'Dunaújváros', 'code' => 'DU'],
            ['name' => 'Eger', 'code' => 'EG'],
            ['name' => 'Érd', 'code' => 'ER'],
            ['name' => 'Fejér County', 'code' => 'FE'],
            ['name' => 'Győr', 'code' => 'GY'],
            ['name' => 'Győr-Moson-Sopron County', 'code' => 'GS'],
            ['name' => 'Hajdú-Bihar County', 'code' => 'HB'],
            ['name' => 'Heves County', 'code' => 'HE'],
            ['name' => 'Hódmezővásárhely', 'code' => 'HV'],
            ['name' => 'Jász-Nagykun-Szolnok County', 'code' => 'JN'],
            ['name' => 'Kaposvár', 'code' => 'KV'],
            ['name' => 'Kecskemét', 'code' => 'KM'],
            ['name' => 'Komárom-Esztergom', 'code' => 'KE'],
            ['name' => 'Miskolc', 'code' => 'MI'],
            ['name' => 'Nagykanizsa', 'code' => 'NK'],
            ['name' => 'Nógrád County', 'code' => 'NO'],
            ['name' => 'Nyíregyháza', 'code' => 'NY'],
            ['name' => 'Pécs', 'code' => 'PS'],
            ['name' => 'Pest County', 'code' => 'PE'],
            ['name' => 'Salgótarján', 'code' => 'ST'],
            ['name' => 'Somogy County', 'code' => 'SO'],
            ['name' => 'Sopron', 'code' => 'SN'],
            ['name' => 'Szabolcs-Szatmár-Bereg County', 'code' => 'SZ'],
            ['name' => 'Szeged', 'code' => 'SD'],
            ['name' => 'Székesfehérvár', 'code' => 'SF'],
            ['name' => 'Szekszárd', 'code' => 'SS'],
            ['name' => 'Szolnok', 'code' => 'SK'],
            ['name' => 'Szombathely', 'code' => 'SH'],
            ['name' => 'Tatabánya', 'code' => 'TB'],
            ['name' => 'Tolna County', 'code' => 'TO'],
            ['name' => 'Vas County', 'code' => 'VA'],
            ['name' => 'Veszprém', 'code' => 'VM'],
            ['name' => 'Veszprém County', 'code' => 'VE'],
            ['name' => 'Zala County', 'code' => 'ZA'],
            ['name' => 'Zalaegerszeg', 'code' => 'ZE'],
        ];
    }
}
