<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Brazil;

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

class BrazilGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_brazil_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.brazil';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'BR';
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
                        label: 'State / Federal District',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['state', 'federal_district'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'municipality',
                        label: 'Municipality',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['municipality', 'district'],
                        areaLevels: [2],
                        parentKey: 'state',
                        assignmentRole: 'municipality',
                    ),
                ],
            ),
        ];
    }

    /** @return array<string, string> */
    public function areaTypeLabels(): array
    {
        // Area names use Portuguese official forms, so the type labels use
        // the Portuguese administrative terms.
        return [
            'state' => 'Estado',
            'federal_district' => 'Distrito Federal',
            'municipality' => 'Município',
            'district' => 'Distrito',
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
                'state' => ['state'],
                'federal_district' => ['state'],
                'municipality' => ['municipality'],
                'district' => ['municipality'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'BR', 'is_primary' => true],
                $areaRoles,
            );
        }

        return $roles;
    }

    /** @return array<string, list<array{name: string, name_type?: string, is_preferred?: bool}>> */
    public function areaNames(AddressCountry $country): array
    {
        // Correios UF abbreviations, mirroring the formatter map.
        return [
            'br:federal_district:distrito-federal' => [
                ['name' => 'DF', 'name_type' => 'abbreviation'],
            ],
            'br:state:acre' => [
                ['name' => 'AC', 'name_type' => 'abbreviation'],
            ],
            'br:state:alagoas' => [
                ['name' => 'AL', 'name_type' => 'abbreviation'],
            ],
            'br:state:amapa' => [
                ['name' => 'AP', 'name_type' => 'abbreviation'],
            ],
            'br:state:amazonas' => [
                ['name' => 'AM', 'name_type' => 'abbreviation'],
            ],
            'br:state:bahia' => [
                ['name' => 'BA', 'name_type' => 'abbreviation'],
            ],
            'br:state:ceara' => [
                ['name' => 'CE', 'name_type' => 'abbreviation'],
            ],
            'br:state:espirito-santo' => [
                ['name' => 'ES', 'name_type' => 'abbreviation'],
            ],
            'br:state:goias' => [
                ['name' => 'GO', 'name_type' => 'abbreviation'],
            ],
            'br:state:maranhao' => [
                ['name' => 'MA', 'name_type' => 'abbreviation'],
            ],
            'br:state:mato-grosso' => [
                ['name' => 'MT', 'name_type' => 'abbreviation'],
            ],
            'br:state:mato-grosso-do-sul' => [
                ['name' => 'MS', 'name_type' => 'abbreviation'],
            ],
            'br:state:minas-gerais' => [
                ['name' => 'MG', 'name_type' => 'abbreviation'],
            ],
            'br:state:para' => [
                ['name' => 'PA', 'name_type' => 'abbreviation'],
            ],
            'br:state:paraiba' => [
                ['name' => 'PB', 'name_type' => 'abbreviation'],
            ],
            'br:state:parana' => [
                ['name' => 'PR', 'name_type' => 'abbreviation'],
            ],
            'br:state:pernambuco' => [
                ['name' => 'PE', 'name_type' => 'abbreviation'],
            ],
            'br:state:piaui' => [
                ['name' => 'PI', 'name_type' => 'abbreviation'],
            ],
            'br:state:rio-de-janeiro' => [
                ['name' => 'RJ', 'name_type' => 'abbreviation'],
            ],
            'br:state:rio-grande-do-norte' => [
                ['name' => 'RN', 'name_type' => 'abbreviation'],
            ],
            'br:state:rio-grande-do-sul' => [
                ['name' => 'RS', 'name_type' => 'abbreviation'],
            ],
            'br:state:rondonia' => [
                ['name' => 'RO', 'name_type' => 'abbreviation'],
            ],
            'br:state:roraima' => [
                ['name' => 'RR', 'name_type' => 'abbreviation'],
            ],
            'br:state:santa-catarina' => [
                ['name' => 'SC', 'name_type' => 'abbreviation'],
            ],
            'br:state:sao-paulo' => [
                ['name' => 'SP', 'name_type' => 'abbreviation'],
            ],
            'br:state:sergipe' => [
                ['name' => 'SE', 'name_type' => 'abbreviation'],
            ],
            'br:state:tocantins' => [
                ['name' => 'TO', 'name_type' => 'abbreviation'],
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
            __DIR__ . '/../../../resources/geography/brazil-address-areas.csv',
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
            'AC' => 'AC',
            'AL' => 'AL',
            'AM' => 'AM',
            'AP' => 'AP',
            'BA' => 'BA',
            'CE' => 'CE',
            'DF' => 'DF',
            'ES' => 'ES',
            'GO' => 'GO',
            'MA' => 'MA',
            'MG' => 'MG',
            'MS' => 'MS',
            'MT' => 'MT',
            'PA' => 'PA',
            'PB' => 'PB',
            'PE' => 'PE',
            'PI' => 'PI',
            'PR' => 'PR',
            'RJ' => 'RJ',
            'RN' => 'RN',
            'RO' => 'RO',
            'RR' => 'RR',
            'RS' => 'RS',
            'SC' => 'SC',
            'SE' => 'SE',
            'SP' => 'SP',
            'TO' => 'TO',
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
            ['name' => 'Acre', 'code' => 'AC'],
            ['name' => 'Alagoas', 'code' => 'AL'],
            ['name' => 'Amazonas', 'code' => 'AM'],
            ['name' => 'Amapá', 'code' => 'AP'],
            ['name' => 'Bahia', 'code' => 'BA'],
            ['name' => 'Ceará', 'code' => 'CE'],
            ['name' => 'Distrito Federal', 'code' => 'DF'],
            ['name' => 'Espírito Santo', 'code' => 'ES'],
            ['name' => 'Goiás', 'code' => 'GO'],
            ['name' => 'Maranhão', 'code' => 'MA'],
            ['name' => 'Minas Gerais', 'code' => 'MG'],
            ['name' => 'Mato Grosso do Sul', 'code' => 'MS'],
            ['name' => 'Mato Grosso', 'code' => 'MT'],
            ['name' => 'Pará', 'code' => 'PA'],
            ['name' => 'Paraíba', 'code' => 'PB'],
            ['name' => 'Pernambuco', 'code' => 'PE'],
            ['name' => 'Piauí', 'code' => 'PI'],
            ['name' => 'Paraná', 'code' => 'PR'],
            ['name' => 'Rio de Janeiro', 'code' => 'RJ'],
            ['name' => 'Rio Grande do Norte', 'code' => 'RN'],
            ['name' => 'Rondônia', 'code' => 'RO'],
            ['name' => 'Roraima', 'code' => 'RR'],
            ['name' => 'Rio Grande do Sul', 'code' => 'RS'],
            ['name' => 'Santa Catarina', 'code' => 'SC'],
            ['name' => 'Sergipe', 'code' => 'SE'],
            ['name' => 'São Paulo', 'code' => 'SP'],
            ['name' => 'Tocantins', 'code' => 'TO'],
        ];
    }
}
