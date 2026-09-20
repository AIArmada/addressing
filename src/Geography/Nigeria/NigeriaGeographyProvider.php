<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Nigeria;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class NigeriaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_nigeria_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.nigeria';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'NG';
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
                        key: 'state',
                        label: 'State',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['state'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'lga',
                        label: 'Local Government Area',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['lga', 'area_council'],
                        areaLevels: [2],
                        parentKey: 'state',
                        assignmentRole: 'lga',
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
                'state' => ['state'],
                'lga', 'area_council' => ['lga'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'NG', 'is_primary' => true],
                $areaRoles,
            );
        }

        return $roles;
    }

    /** @return array<string, list<array{name: string, name_type?: string, is_preferred?: bool}>> */
    public function areaNames(AddressCountry $country): array
    {
        return [
            'ng:lga:ebonyi:afikpo' => [
                ['name' => 'Afikpo North', 'name_type' => 'historic'],
            ],
            'ng:lga:ebonyi:edda' => [
                ['name' => 'Afikpo South', 'name_type' => 'historic'],
            ],
            'ng:lga:ekiti:aiyekire' => [
                ['name' => 'Gbonyin', 'name_type' => 'common', 'is_preferred' => true],
                ['name' => 'Ayekire', 'name_type' => 'alternative'],
            ],
            'ng:lga:kano:ghari' => [
                ['name' => 'Kunchi', 'name_type' => 'historic'],
            ],
            'ng:lga:ogun:yewa-north' => [
                ['name' => 'Egbado North', 'name_type' => 'historic'],
            ],
            'ng:lga:ogun:yewa-south' => [
                ['name' => 'Egbado South', 'name_type' => 'historic'],
            ],
            'ng:lga:oyo:atisbo' => [
                ['name' => 'Atigbo', 'name_type' => 'historic'],
            ],
            'ng:lga:rivers:obio-akpor' => [
                ['name' => 'Obia/Akpor', 'name_type' => 'historic'],
                ['name' => 'Abio/Akpor', 'name_type' => 'alternative'],
            ],
            'ng:lga:benue:oturkpo' => [
                ['name' => 'Otukpo', 'name_type' => 'common'],
            ],
            'ng:lga:ondo:ile-oluji-okeigbo' => [
                ['name' => 'Ile-Oluji', 'name_type' => 'common'],
            ],
            'ng:area_council:abuja-federal-capital-territory:abuja-municipal' => [
                ['name' => 'Abuja Municipal Area Council', 'name_type' => 'official'],
                ['name' => 'AMAC', 'name_type' => 'abbreviation'],
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
            __DIR__ . '/../../../resources/geography/nigeria-address-areas.csv',
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
            'AD' => 'AD',
            'AK' => 'AK',
            'AN' => 'AN',
            'BA' => 'BA',
            'BE' => 'BE',
            'BO' => 'BO',
            'BY' => 'BY',
            'CR' => 'CR',
            'DE' => 'DE',
            'EB' => 'EB',
            'ED' => 'ED',
            'EK' => 'EK',
            'EN' => 'EN',
            'FC' => 'FC',
            'GO' => 'GO',
            'IM' => 'IM',
            'JI' => 'JI',
            'KD' => 'KD',
            'KE' => 'KE',
            'KN' => 'KN',
            'KO' => 'KO',
            'KT' => 'KT',
            'KW' => 'KW',
            'LA' => 'LA',
            'NA' => 'NA',
            'NI' => 'NI',
            'OG' => 'OG',
            'ON' => 'ON',
            'OS' => 'OS',
            'OY' => 'OY',
            'PL' => 'PL',
            'RI' => 'RI',
            'SO' => 'SO',
            'TA' => 'TA',
            'YO' => 'YO',
            'ZA' => 'ZA',
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
            ['name' => 'Abia', 'code' => 'AB'],
            ['name' => 'Adamawa', 'code' => 'AD'],
            ['name' => 'Akwa Ibom', 'code' => 'AK'],
            ['name' => 'Anambra', 'code' => 'AN'],
            ['name' => 'Bauchi', 'code' => 'BA'],
            ['name' => 'Benue', 'code' => 'BE'],
            ['name' => 'Borno', 'code' => 'BO'],
            ['name' => 'Bayelsa', 'code' => 'BY'],
            ['name' => 'Cross River', 'code' => 'CR'],
            ['name' => 'Delta', 'code' => 'DE'],
            ['name' => 'Ebonyi', 'code' => 'EB'],
            ['name' => 'Edo', 'code' => 'ED'],
            ['name' => 'Ekiti', 'code' => 'EK'],
            ['name' => 'Enugu', 'code' => 'EN'],
            ['name' => 'Abuja Federal Capital Territory', 'code' => 'FC'],
            ['name' => 'Gombe', 'code' => 'GO'],
            ['name' => 'Imo', 'code' => 'IM'],
            ['name' => 'Jigawa', 'code' => 'JI'],
            ['name' => 'Kaduna', 'code' => 'KD'],
            ['name' => 'Kebbi', 'code' => 'KE'],
            ['name' => 'Kano', 'code' => 'KN'],
            ['name' => 'Kogi', 'code' => 'KO'],
            ['name' => 'Katsina', 'code' => 'KT'],
            ['name' => 'Kwara', 'code' => 'KW'],
            ['name' => 'Lagos', 'code' => 'LA'],
            ['name' => 'Nasarawa', 'code' => 'NA'],
            ['name' => 'Niger', 'code' => 'NI'],
            ['name' => 'Ogun', 'code' => 'OG'],
            ['name' => 'Ondo', 'code' => 'ON'],
            ['name' => 'Osun', 'code' => 'OS'],
            ['name' => 'Oyo', 'code' => 'OY'],
            ['name' => 'Plateau', 'code' => 'PL'],
            ['name' => 'Rivers', 'code' => 'RI'],
            ['name' => 'Sokoto', 'code' => 'SO'],
            ['name' => 'Taraba', 'code' => 'TA'],
            ['name' => 'Yobe', 'code' => 'YO'],
            ['name' => 'Zamfara', 'code' => 'ZA'],
        ];
    }
}
