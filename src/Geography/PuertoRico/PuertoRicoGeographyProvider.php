<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\PuertoRico;

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
use AIArmada\Addressing\Support\UsZipCodeKeys;

class PuertoRicoGeographyProvider implements CountryAddressAreaMetadataProvider, CountryAreaTypeLabelProvider, CountryGeographyProvider, CountryHierarchyProvider, CountryPostalCodeNormalizer
{
    public const string AREA_SOURCE = 'aiarmada_addressing_puerto_rico_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.puerto_rico';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'PR';
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

        // Ten municipios were mistyped as regions with invented 2-letter
        // codes. Delete stragglers seeded before the FIPS fix so reseeds
        // converge on the 78 FIPS-coded municipios.
        $stateClass::query()
            ->where('country_id', $country->id)
            ->whereIn('code', ['AR', 'BY', 'CG', 'CL', 'GN', 'MG', 'PO', 'SJ', 'TB', 'TA'])
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
                        label: 'Municipality',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['municipality'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'barrio',
                        label: 'Barrio / Barrio-pueblo',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['barrio', 'barrio_pueblo'],
                        areaLevels: [2],
                        parentKey: 'municipality',
                        assignmentRole: 'barrio',
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
            'municipality' => 'Municipio',
            'barrio_pueblo' => 'Barrio-Pueblo',
            'barrio' => 'Barrio',
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
                'barrio' => ['barrio'],
                'barrio_pueblo' => ['barrio'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'PR', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/puerto-rico-address-areas.csv',
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
            '001' => '001',
            '003' => '003',
            '005' => '005',
            '007' => '007',
            '009' => '009',
            '011' => '011',
            '013' => '013',
            '015' => '015',
            '017' => '017',
            '019' => '019',
            '021' => '021',
            '023' => '023',
            '025' => '025',
            '027' => '027',
            '029' => '029',
            '031' => '031',
            '033' => '033',
            '035' => '035',
            '037' => '037',
            '039' => '039',
            '041' => '041',
            '043' => '043',
            '045' => '045',
            '047' => '047',
            '049' => '049',
            '051' => '051',
            '053' => '053',
            '054' => '054',
            '055' => '055',
            '057' => '057',
            '059' => '059',
            '061' => '061',
            '063' => '063',
            '065' => '065',
            '067' => '067',
            '069' => '069',
            '071' => '071',
            '073' => '073',
            '075' => '075',
            '077' => '077',
            '079' => '079',
            '081' => '081',
            '083' => '083',
            '085' => '085',
            '087' => '087',
            '089' => '089',
            '091' => '091',
            '093' => '093',
            '095' => '095',
            '097' => '097',
            '099' => '099',
            '101' => '101',
            '103' => '103',
            '105' => '105',
            '107' => '107',
            '109' => '109',
            '111' => '111',
            '113' => '113',
            '115' => '115',
            '117' => '117',
            '119' => '119',
            '121' => '121',
            '123' => '123',
            '125' => '125',
            '127' => '127',
            '129' => '129',
            '131' => '131',
            '133' => '133',
            '135' => '135',
            '137' => '137',
            '139' => '139',
            '141' => '141',
            '143' => '143',
            '145' => '145',
            '147' => '147',
            '149' => '149',
            '151' => '151',
            '153' => '153',
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
            ['name' => 'Adjuntas', 'code' => '001'],
            ['name' => 'Aguada', 'code' => '003'],
            ['name' => 'Aguadilla', 'code' => '005'],
            ['name' => 'Aguas Buenas', 'code' => '007'],
            ['name' => 'Aibonito', 'code' => '009'],
            ['name' => 'Añasco', 'code' => '011'],
            ['name' => 'Arecibo', 'code' => '013'],
            ['name' => 'Arroyo', 'code' => '015'],
            ['name' => 'Barceloneta', 'code' => '017'],
            ['name' => 'Barranquitas', 'code' => '019'],
            ['name' => 'Bayamón', 'code' => '021'],
            ['name' => 'Cabo Rojo', 'code' => '023'],
            ['name' => 'Caguas', 'code' => '025'],
            ['name' => 'Camuy', 'code' => '027'],
            ['name' => 'Canóvanas', 'code' => '029'],
            ['name' => 'Carolina', 'code' => '031'],
            ['name' => 'Cataño', 'code' => '033'],
            ['name' => 'Cayey', 'code' => '035'],
            ['name' => 'Ceiba', 'code' => '037'],
            ['name' => 'Ciales', 'code' => '039'],
            ['name' => 'Cidra', 'code' => '041'],
            ['name' => 'Coamo', 'code' => '043'],
            ['name' => 'Comerío', 'code' => '045'],
            ['name' => 'Corozal', 'code' => '047'],
            ['name' => 'Culebra', 'code' => '049'],
            ['name' => 'Dorado', 'code' => '051'],
            ['name' => 'Fajardo', 'code' => '053'],
            ['name' => 'Florida', 'code' => '054'],
            ['name' => 'Guánica', 'code' => '055'],
            ['name' => 'Guayama', 'code' => '057'],
            ['name' => 'Guayanilla', 'code' => '059'],
            ['name' => 'Guaynabo', 'code' => '061'],
            ['name' => 'Gurabo', 'code' => '063'],
            ['name' => 'Hatillo', 'code' => '065'],
            ['name' => 'Hormigueros', 'code' => '067'],
            ['name' => 'Humacao', 'code' => '069'],
            ['name' => 'Isabela', 'code' => '071'],
            ['name' => 'Jayuya', 'code' => '073'],
            ['name' => 'Juana Díaz', 'code' => '075'],
            ['name' => 'Juncos', 'code' => '077'],
            ['name' => 'Lajas', 'code' => '079'],
            ['name' => 'Lares', 'code' => '081'],
            ['name' => 'Las Marías', 'code' => '083'],
            ['name' => 'Las Piedras', 'code' => '085'],
            ['name' => 'Loíza', 'code' => '087'],
            ['name' => 'Luquillo', 'code' => '089'],
            ['name' => 'Manatí', 'code' => '091'],
            ['name' => 'Maricao', 'code' => '093'],
            ['name' => 'Maunabo', 'code' => '095'],
            ['name' => 'Mayagüez', 'code' => '097'],
            ['name' => 'Moca', 'code' => '099'],
            ['name' => 'Morovis', 'code' => '101'],
            ['name' => 'Naguabo', 'code' => '103'],
            ['name' => 'Naranjito', 'code' => '105'],
            ['name' => 'Orocovis', 'code' => '107'],
            ['name' => 'Patillas', 'code' => '109'],
            ['name' => 'Peñuelas', 'code' => '111'],
            ['name' => 'Ponce', 'code' => '113'],
            ['name' => 'Quebradillas', 'code' => '115'],
            ['name' => 'Rincón', 'code' => '117'],
            ['name' => 'Río Grande', 'code' => '119'],
            ['name' => 'Sabana Grande', 'code' => '121'],
            ['name' => 'Salinas', 'code' => '123'],
            ['name' => 'San Germán', 'code' => '125'],
            ['name' => 'San Juan', 'code' => '127'],
            ['name' => 'San Lorenzo', 'code' => '129'],
            ['name' => 'San Sebastián', 'code' => '131'],
            ['name' => 'Santa Isabel', 'code' => '133'],
            ['name' => 'Toa Alta', 'code' => '135'],
            ['name' => 'Toa Baja', 'code' => '137'],
            ['name' => 'Trujillo Alto', 'code' => '139'],
            ['name' => 'Utuado', 'code' => '141'],
            ['name' => 'Vega Alta', 'code' => '143'],
            ['name' => 'Vega Baja', 'code' => '145'],
            ['name' => 'Vieques', 'code' => '147'],
            ['name' => 'Villalba', 'code' => '149'],
            ['name' => 'Yabucoa', 'code' => '151'],
            ['name' => 'Yauco', 'code' => '153'],
        ];
    }
}
