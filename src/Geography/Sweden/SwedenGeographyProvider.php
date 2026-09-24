<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Sweden;

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

class SwedenGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider, CountryPostalCodeNormalizer
{
    public const string AREA_SOURCE = 'aiarmada_addressing_sweden_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.sweden';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'SE';
    }

    /** @return list<string> */
    public function postalCodeLookupKeys(string $code): array
    {
        $code = mb_strtoupper(mb_trim($code));

        // Bundled codes carry the official space; spaceless input gains it.
        if (preg_match('/^(\d{3})(\d{2})$/', $code, $matches) === 1) {
            return [$code, $matches[1] . ' ' . $matches[2]];
        }

        return [$code];
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
                        label: 'County',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['county'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'municipality',
                        label: 'Municipality',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['municipality'],
                        areaLevels: [2],
                        parentKey: 'county',
                        assignmentRole: 'municipality',
                    ),
                ],
            ),
        ];
    }

    /** @return array<string, string> */
    public function areaTypeLabels(): array
    {
        // Swedish administrative terms.
        return [
            'county' => 'Län',
            'municipality' => 'Kommun',
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
                'municipality' => ['municipality'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'SE', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/sweden-address-areas.csv',
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
            'K' => 'K',
            'W' => 'W',
            'X' => 'X',
            'I' => 'I',
            'N' => 'N',
            'Z' => 'Z',
            'F' => 'F',
            'H' => 'H',
            'G' => 'G',
            'BD' => 'BD',
            'T' => 'T',
            'E' => 'E',
            'M' => 'M',
            'D' => 'D',
            'AB' => 'AB',
            'C' => 'C',
            'S' => 'S',
            'AC' => 'AC',
            'Y' => 'Y',
            'U' => 'U',
            'O' => 'O',
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
            ['name' => 'Blekinge', 'code' => 'K'],
            ['name' => 'Dalarna', 'code' => 'W'],
            ['name' => 'Gävleborg', 'code' => 'X'],
            ['name' => 'Gotland', 'code' => 'I'],
            ['name' => 'Halland', 'code' => 'N'],
            ['name' => 'Jämtland', 'code' => 'Z'],
            ['name' => 'Jönköping', 'code' => 'F'],
            ['name' => 'Kalmar', 'code' => 'H'],
            ['name' => 'Kronoberg', 'code' => 'G'],
            ['name' => 'Norrbotten', 'code' => 'BD'],
            ['name' => 'Örebro', 'code' => 'T'],
            ['name' => 'Östergötland', 'code' => 'E'],
            ['name' => 'Skåne', 'code' => 'M'],
            ['name' => 'Södermanland', 'code' => 'D'],
            ['name' => 'Stockholm', 'code' => 'AB'],
            ['name' => 'Uppsala', 'code' => 'C'],
            ['name' => 'Värmland', 'code' => 'S'],
            ['name' => 'Västerbotten', 'code' => 'AC'],
            ['name' => 'Västernorrland', 'code' => 'Y'],
            ['name' => 'Västmanland', 'code' => 'U'],
            ['name' => 'Västra Götaland', 'code' => 'O'],
        ];
    }
}
