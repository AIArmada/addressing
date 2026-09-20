<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Bangladesh;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class BangladeshGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_bangladesh_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.bangladesh';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'BD';
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
                        key: 'division',
                        label: 'Division',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['division'],
                        areaLevel: 1,
                    ),
                    new AddressLevelDefinition(
                        key: 'district',
                        label: 'District',
                        kind: 'area',
                        hierarchyType: 'administrative',
                        areaTypes: ['district'],
                        areaLevels: [2],
                        parentKey: 'division',
                        assignmentRole: 'district',
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
                'division' => ['division'],
                'district' => ['district'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'BD', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/bangladesh-address-areas.csv',
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
            ['name' => 'Barishal', 'code' => 'A'],
            ['name' => 'Chattogram', 'code' => 'B'],
            ['name' => 'Dhaka', 'code' => 'C'],
            ['name' => 'Khulna', 'code' => 'D'],
            ['name' => 'Rajshahi', 'code' => 'E'],
            ['name' => 'Rangpur', 'code' => 'F'],
            ['name' => 'Sylhet', 'code' => 'G'],
            ['name' => 'Mymensingh', 'code' => 'H'],
            ['name' => 'Bandarban', 'code' => '01'],
            ['name' => 'Barguna', 'code' => '02'],
            ['name' => 'Bogura', 'code' => '03'],
            ['name' => 'Brahmanbaria', 'code' => '04'],
            ['name' => 'Bagerhat', 'code' => '05'],
            ['name' => 'Barishal', 'code' => '06'],
            ['name' => 'Bhola', 'code' => '07'],
            ['name' => 'Cumilla', 'code' => '08'],
            ['name' => 'Chandpur', 'code' => '09'],
            ['name' => 'Chattogram', 'code' => '10'],
            ['name' => 'Cox\'s Bazar', 'code' => '11'],
            ['name' => 'Chuadanga', 'code' => '12'],
            ['name' => 'Dhaka', 'code' => '13'],
            ['name' => 'Dinajpur', 'code' => '14'],
            ['name' => 'Faridpur', 'code' => '15'],
            ['name' => 'Feni', 'code' => '16'],
            ['name' => 'Gopalganj', 'code' => '17'],
            ['name' => 'Gazipur', 'code' => '18'],
            ['name' => 'Gaibandha', 'code' => '19'],
            ['name' => 'Habiganj', 'code' => '20'],
            ['name' => 'Jamalpur', 'code' => '21'],
            ['name' => 'Jashore', 'code' => '22'],
            ['name' => 'Jhenaidah', 'code' => '23'],
            ['name' => 'Joypurhat', 'code' => '24'],
            ['name' => 'Jhalakathi', 'code' => '25'],
            ['name' => 'Kishoreganj', 'code' => '26'],
            ['name' => 'Khulna', 'code' => '27'],
            ['name' => 'Kurigram', 'code' => '28'],
            ['name' => 'Khagrachhari', 'code' => '29'],
            ['name' => 'Kushtia', 'code' => '30'],
            ['name' => 'Lakshmipur', 'code' => '31'],
            ['name' => 'Lalmonirhat', 'code' => '32'],
            ['name' => 'Manikganj', 'code' => '33'],
            ['name' => 'Mymensingh', 'code' => '34'],
            ['name' => 'Munshiganj', 'code' => '35'],
            ['name' => 'Madaripur', 'code' => '36'],
            ['name' => 'Magura', 'code' => '37'],
            ['name' => 'Moulvibazar', 'code' => '38'],
            ['name' => 'Meherpur', 'code' => '39'],
            ['name' => 'Narayanganj', 'code' => '40'],
            ['name' => 'Netrokona', 'code' => '41'],
            ['name' => 'Narsingdi', 'code' => '42'],
            ['name' => 'Narail', 'code' => '43'],
            ['name' => 'Natore', 'code' => '44'],
            ['name' => 'Chapai Nawabganj', 'code' => '45'],
            ['name' => 'Nilphamari', 'code' => '46'],
            ['name' => 'Noakhali', 'code' => '47'],
            ['name' => 'Naogaon', 'code' => '48'],
            ['name' => 'Pabna', 'code' => '49'],
            ['name' => 'Pirojpur', 'code' => '50'],
            ['name' => 'Patuakhali', 'code' => '51'],
            ['name' => 'Panchagarh', 'code' => '52'],
            ['name' => 'Rajbari', 'code' => '53'],
            ['name' => 'Rajshahi', 'code' => '54'],
            ['name' => 'Rangpur', 'code' => '55'],
            ['name' => 'Rangamati', 'code' => '56'],
            ['name' => 'Sherpur', 'code' => '57'],
            ['name' => 'Satkhira', 'code' => '58'],
            ['name' => 'Sirajganj', 'code' => '59'],
            ['name' => 'Sylhet', 'code' => '60'],
            ['name' => 'Sunamganj', 'code' => '61'],
            ['name' => 'Shariatpur', 'code' => '62'],
            ['name' => 'Tangail', 'code' => '63'],
            ['name' => 'Thakurgaon', 'code' => '64'],
        ];
    }
}
