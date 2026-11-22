<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

/**
 * Resolves translated display labels for a country's configured address hierarchy level.
 */
final class LocationLevelLabels
{
    /**
     * @param  array<string, ?string>  $areaIds
     */
    public static function label(
        int | string | null $countryId,
        string $storageColumn,
        string $fallback,
        int | string | null $stateId = null,
        array $areaIds = [],
    ): string {
        $countryId = self::normalizeLocationId($countryId);

        if ($countryId === null) {
            return $fallback;
        }

        $label = app(CountryAddressProfileResolver::class)->levelLabel(
            $countryId,
            $storageColumn,
            self::normalizeLocationId($stateId),
            $areaIds,
        );

        if ($label === null) {
            return $fallback;
        }

        if (Lang::hasForLocale($label)) {
            return __($label);
        }

        return implode(' / ', array_map(
            static fn (string $typeLabel): string => __($typeLabel),
            explode(' / ', $label),
        ));
    }

    private static function normalizeLocationId(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = mb_trim($value);

        if ($trimmed === '') {
            return null;
        }

        return Str::isUuid($trimmed) ? $trimmed : null;
    }
}
