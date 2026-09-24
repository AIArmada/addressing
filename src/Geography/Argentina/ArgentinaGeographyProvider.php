<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Argentina;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryAreaTypeLabelProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Contracts\CountryPostalCodeNormalizer;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class ArgentinaGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider, CountryPostalCodeNormalizer
{
    public const string AREA_SOURCE = 'aiarmada_addressing_argentina_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.argentina';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'AR';
    }

    /** @return list<string> */
    public function postalCodeLookupKeys(string $code): array
    {
        $code = mb_strtoupper((string) preg_replace('/\s+/', '', mb_trim($code)));
        $keys = [$code];

        // Full 8-char CPA: province letter + 4-digit base + 3 block-face
        // letters. Bundled codes are base level (interior 4-digit, CABA
        // C+4-digit); the block face carries no L2 signal.
        if (preg_match('/^([A-Z])(\d{4})[A-Z]{3}$/', $code, $matches) === 1) {
            $keys[] = $matches[1] === 'C' ? 'C' . $matches[2] : $matches[2];
        }

        return $keys;
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
                        label: 'Province / Autonomous City',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['province', 'city'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'department',
                        label: 'Department / Partido / Commune',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['department', 'partido', 'commune'],
                        areaLevels: [2],
                        parentKey: 'province',
                        assignmentRole: 'department',
                    ),
                ],
            ),
        ];
    }

    /** @return array<string, string> */
    public function areaTypeLabels(): array
    {
        // Area names use Spanish official forms (partido already renders correctly).
        return [
            'province' => 'Provincia',
            'city' => 'Ciudad',
            'commune' => 'Comuna',
            'department' => 'Departamento',
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
                'province' => ['province'],
                'city' => ['province'],
                'department' => ['department'],
                'partido' => ['department'],
                'commune' => ['department'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'AR', 'is_primary' => true],
                $areaRoles,
            );
        }

        return $roles;
    }

    /** @return array<string, list<array{name: string, name_type?: string, is_preferred?: bool}>> */
    public function areaNames(AddressCountry $country): array
    {
        return [
            'ar:city:autonomous-city-of-buenos-aires' => [
                ['name' => 'Autonomous City of Buenos Aires', 'name_type' => 'alternative'],
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
            __DIR__ . '/../../../resources/geography/argentina-address-areas.csv',
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
            'A' => 'A',
            'B' => 'B',
            'C' => 'C',
            'D' => 'D',
            'E' => 'E',
            'F' => 'F',
            'G' => 'G',
            'H' => 'H',
            'J' => 'J',
            'K' => 'K',
            'L' => 'L',
            'M' => 'M',
            'N' => 'N',
            'P' => 'P',
            'Q' => 'Q',
            'R' => 'R',
            'S' => 'S',
            'T' => 'T',
            'U' => 'U',
            'V' => 'V',
            'W' => 'W',
            'X' => 'X',
            'Y' => 'Y',
            'Z' => 'Z',
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
            ['name' => 'Salta', 'code' => 'A'],
            ['name' => 'Buenos Aires', 'code' => 'B'],
            ['name' => 'Ciudad Autónoma de Buenos Aires', 'code' => 'C'],
            ['name' => 'San Luis', 'code' => 'D'],
            ['name' => 'Entre Ríos', 'code' => 'E'],
            ['name' => 'La Rioja', 'code' => 'F'],
            ['name' => 'Santiago del Estero', 'code' => 'G'],
            ['name' => 'Chaco', 'code' => 'H'],
            ['name' => 'San Juan', 'code' => 'J'],
            ['name' => 'Catamarca', 'code' => 'K'],
            ['name' => 'La Pampa', 'code' => 'L'],
            ['name' => 'Mendoza', 'code' => 'M'],
            ['name' => 'Misiones', 'code' => 'N'],
            ['name' => 'Formosa', 'code' => 'P'],
            ['name' => 'Neuquén', 'code' => 'Q'],
            ['name' => 'Río Negro', 'code' => 'R'],
            ['name' => 'Santa Fe', 'code' => 'S'],
            ['name' => 'Tucumán', 'code' => 'T'],
            ['name' => 'Chubut', 'code' => 'U'],
            ['name' => 'Tierra del Fuego', 'code' => 'V'],
            ['name' => 'Corrientes', 'code' => 'W'],
            ['name' => 'Córdoba', 'code' => 'X'],
            ['name' => 'Jujuy', 'code' => 'Y'],
            ['name' => 'Santa Cruz', 'code' => 'Z'],
        ];
    }
}
