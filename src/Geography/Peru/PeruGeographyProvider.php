<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Peru;

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

class PeruGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_peru_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.peru';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'PE';
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
                        label: 'Region / Municipality',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['region', 'municipality'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'province',
                        label: 'Province',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['province'],
                        areaLevels: [2],
                        parentKey: 'region',
                        assignmentRole: 'province',
                    ),
                ],
            ),
        ];
    }

    /** @return array<string, string> */
    public function areaTypeLabels(): array
    {
        // Spanish administrative terms.
        return [
            'region' => 'Región',
            'municipality' => 'Municipalidad',
            'province' => 'Provincia',
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
                'region' => ['region'],
                'municipality' => ['region'],
                'province' => ['province'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'PE', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/peru-address-areas.csv',
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
            'AMA' => 'AMA',
            'ANC' => 'ANC',
            'APU' => 'APU',
            'ARE' => 'ARE',
            'AYA' => 'AYA',
            'CAJ' => 'CAJ',
            'CAL' => 'CAL',
            'CUS' => 'CUS',
            'HUC' => 'HUC',
            'HUV' => 'HUV',
            'ICA' => 'ICA',
            'JUN' => 'JUN',
            'LAL' => 'LAL',
            'LAM' => 'LAM',
            'LIM' => 'LIM',
            'LMA' => 'LMA',
            'LOR' => 'LOR',
            'MDD' => 'MDD',
            'MOQ' => 'MOQ',
            'PAS' => 'PAS',
            'PIU' => 'PIU',
            'PUN' => 'PUN',
            'SAM' => 'SAM',
            'TAC' => 'TAC',
            'TUM' => 'TUM',
            'UCA' => 'UCA',
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
            ['name' => 'Amazonas', 'code' => 'AMA'],
            ['name' => 'Áncash', 'code' => 'ANC'],
            ['name' => 'Apurímac', 'code' => 'APU'],
            ['name' => 'Arequipa', 'code' => 'ARE'],
            ['name' => 'Ayacucho', 'code' => 'AYA'],
            ['name' => 'Cajamarca', 'code' => 'CAJ'],
            ['name' => 'Callao', 'code' => 'CAL'],
            ['name' => 'Cusco', 'code' => 'CUS'],
            ['name' => 'Huánuco', 'code' => 'HUC'],
            ['name' => 'Huancavelica', 'code' => 'HUV'],
            ['name' => 'Ica', 'code' => 'ICA'],
            ['name' => 'Junín', 'code' => 'JUN'],
            ['name' => 'La Libertad', 'code' => 'LAL'],
            ['name' => 'Lambayeque', 'code' => 'LAM'],
            ['name' => 'Lima', 'code' => 'LIM'],
            ['name' => 'Municipalidad Metropolitana de Lima', 'code' => 'LMA'],
            ['name' => 'Loreto', 'code' => 'LOR'],
            ['name' => 'Madre de Dios', 'code' => 'MDD'],
            ['name' => 'Moquegua', 'code' => 'MOQ'],
            ['name' => 'Pasco', 'code' => 'PAS'],
            ['name' => 'Piura', 'code' => 'PIU'],
            ['name' => 'Puno', 'code' => 'PUN'],
            ['name' => 'San Martín', 'code' => 'SAM'],
            ['name' => 'Tacna', 'code' => 'TAC'],
            ['name' => 'Tumbes', 'code' => 'TUM'],
            ['name' => 'Ucayali', 'code' => 'UCA'],
        ];
    }
}
