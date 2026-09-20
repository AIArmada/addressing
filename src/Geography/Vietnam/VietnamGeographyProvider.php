<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Vietnam;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use AIArmada\Addressing\Support\ModelResolver;

class VietnamGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_vietnam_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.vietnam';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'VN';
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
                        label: 'Province / Municipality',
                        kind: 'state',
                        hierarchyType: 'administrative',
                        areaTypes: ['province', 'municipality'],
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
                'province' => ['province'],
                'municipality' => ['province'],
                default => [],
            };

            $roles[$area->sourceId] = array_map(
                static fn (string $role): array => ['role' => $role, 'country_code' => 'VN', 'is_primary' => true],
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
            __DIR__ . '/../../../resources/geography/vietnam-address-areas.csv',
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
            '01' => '01',
            '02' => '02',
            '04' => '04',
            '05' => '05',
            '07' => '07',
            '09' => '09',
            '13' => '13',
            '18' => '18',
            '21' => '21',
            '22' => '22',
            '23' => '23',
            '25' => '25',
            '26' => '26',
            '29' => '29',
            '30' => '30',
            '33' => '33',
            '34' => '34',
            '35' => '35',
            '37' => '37',
            '39' => '39',
            '44' => '44',
            '45' => '45',
            '49' => '49',
            '56' => '56',
            '59' => '59',
            '66' => '66',
            '68' => '68',
            '69' => '69',
            '71' => '71',
            'CT' => 'CT',
            'DN' => 'DN',
            'HN' => 'HN',
            'HP' => 'HP',
            'SG' => 'SG',
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
            ['name' => 'Lai Châu', 'code' => '01'],
            ['name' => 'Lào Cai', 'code' => '02'],
            ['name' => 'Cao Bằng', 'code' => '04'],
            ['name' => 'Sơn La', 'code' => '05'],
            ['name' => 'Tuyên Quang', 'code' => '07'],
            ['name' => 'Lạng Sơn', 'code' => '09'],
            ['name' => 'Quảng Ninh', 'code' => '13'],
            ['name' => 'Ninh Bình', 'code' => '18'],
            ['name' => 'Thanh Hóa', 'code' => '21'],
            ['name' => 'Nghệ An', 'code' => '22'],
            ['name' => 'Hà Tĩnh', 'code' => '23'],
            ['name' => 'Quảng Trị', 'code' => '25'],
            ['name' => 'Huế', 'code' => '26'],
            ['name' => 'Quảng Ngãi', 'code' => '29'],
            ['name' => 'Gia Lai', 'code' => '30'],
            ['name' => 'Đắk Lắk', 'code' => '33'],
            ['name' => 'Khánh Hòa', 'code' => '34'],
            ['name' => 'Lâm Đồng', 'code' => '35'],
            ['name' => 'Tây Ninh', 'code' => '37'],
            ['name' => 'Đồng Nai', 'code' => '39'],
            ['name' => 'An Giang', 'code' => '44'],
            ['name' => 'Đồng Tháp', 'code' => '45'],
            ['name' => 'Vĩnh Long', 'code' => '49'],
            ['name' => 'Bắc Ninh', 'code' => '56'],
            ['name' => 'Cà Mau', 'code' => '59'],
            ['name' => 'Hưng Yên', 'code' => '66'],
            ['name' => 'Phú Thọ', 'code' => '68'],
            ['name' => 'Thái Nguyên', 'code' => '69'],
            ['name' => 'Điện Biên', 'code' => '71'],
            ['name' => 'Cần Thơ', 'code' => 'CT'],
            ['name' => 'Đà Nẵng', 'code' => 'DN'],
            ['name' => 'Hà Nội', 'code' => 'HN'],
            ['name' => 'Hải Phòng', 'code' => 'HP'],
            ['name' => 'Hồ Chí Minh', 'code' => 'SG'],
        ];
    }
}
