<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\Geography\Support;

use AIArmada\Addressing\Data\AddressHierarchyDefinition;

/**
 * Maps provider hierarchy levels to Google address-component types.
 */
final class GooglePlaceComponentMapper
{
    /**
     * Ordered Google address-component types whose value should name a level.
     *
     * @return list<string>
     */
    public static function componentTypesForLevel(AddressHierarchyDefinition $hierarchy, int $areaDepth): array
    {
        if ($hierarchy->key === 'postal') {
            return ['locality', 'postal_town', 'sublocality_level_1', 'sublocality_level_2', 'sublocality', 'neighborhood'];
        }

        $primary = 'administrative_area_level_' . ($areaDepth + 2);
        $orderedAdmin = [$primary];

        for ($level = 2; $level <= 7; $level++) {
            $type = "administrative_area_level_{$level}";

            if ($type !== $primary) {
                $orderedAdmin[] = $type;
            }
        }

        if ($areaDepth >= 2) {
            return [
                $primary,
                'locality',
                'postal_town',
                ...array_slice($orderedAdmin, 1),
                'sublocality_level_1',
                'sublocality_level_2',
                'sublocality',
                'neighborhood',
            ];
        }

        return [
            ...$orderedAdmin,
            'locality',
            'postal_town',
            'sublocality_level_1',
            'sublocality_level_2',
            'sublocality',
            'neighborhood',
        ];
    }

    /**
     * Leading administrative generics Google omits from area names.
     *
     * @return list<string>
     */
    public static function namePrefixesToStrip(string $countryCode): array
    {
        return match (mb_strtoupper(mb_trim($countryCode))) {
            'ID' => ['kabupaten', 'kota', 'kecamatan', 'kelurahan', 'provinsi', 'administrasi', 'dki', 'di'],
            default => [],
        };
    }
}
