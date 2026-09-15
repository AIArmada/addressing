<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Models\AddressArea;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Models\City;
use AIArmada\Addressing\Models\State;
use Illuminate\Support\Str;

/**
 * Canonical location names as slug segments.
 *
 * Resolves area, city, state, and country names from the global geography
 * reference data so hosts can build location-disambiguated slugs
 * (`grand-hall-kuala-lumpur-my`) from canonical names rather than free-text
 * input. Literal address strings always win; reference lookups are the
 * fallback.
 */
final class LocationSlugSegments
{
    /**
     * Build the location suffix (`city-state-countrycode`) for an address.
     *
     * Each level prefers the literal address string, then the canonical name
     * for the referenced geography id, then (for city/state) the name of the
     * assigned area id. Consecutive duplicate segments collapse to one.
     *
     * @param  array<string, mixed>  $address
     * @param  mixed  $cityAreaId  Area id for the city fallback. Non-string values resolve to null.
     * @param  mixed  $stateAreaId  Area id for the state fallback. Non-string values resolve to null.
     */
    public static function suffix(array $address, mixed $cityAreaId = null, mixed $stateAreaId = null, bool $preferLiteralCountry = true): string
    {
        $city = self::firstFilled([
            $address['city'] ?? null,
            self::cityName($address['city_id'] ?? null),
            self::areaName($cityAreaId),
        ]);
        $state = self::firstFilled([
            $address['state'] ?? null,
            self::stateName($address['state_id'] ?? null),
            self::areaName($stateAreaId),
        ]);
        $countryCode = self::countryCode($address, $preferLiteralCountry);

        $segments = [];

        foreach ([
            self::slugSegment($city),
            self::slugSegment($state),
            self::codeSegment($countryCode),
        ] as $segment) {
            if ($segment === null) {
                continue;
            }

            if (($segments[array_key_last($segments)] ?? null) === $segment) {
                continue;
            }

            $segments[] = $segment;
        }

        return implode('-', $segments);
    }

    public static function areaName(mixed $areaId): ?string
    {
        $areaId = self::uuidValue($areaId);

        if ($areaId === null) {
            return null;
        }

        $resolved = AddressArea::query()->whereKey($areaId)->value('name');

        return is_string($resolved) && mb_trim($resolved) !== '' ? $resolved : null;
    }

    public static function cityName(mixed $cityId): ?string
    {
        $cityId = self::uuidValue($cityId);

        if ($cityId === null) {
            return null;
        }

        $resolved = City::query()->whereKey($cityId)->value('name');

        return is_string($resolved) && mb_trim($resolved) !== '' ? $resolved : null;
    }

    public static function stateName(mixed $stateId): ?string
    {
        $stateId = self::uuidValue($stateId);

        if ($stateId === null) {
            return null;
        }

        $resolved = State::query()->whereKey($stateId)->value('name');

        return is_string($resolved) && mb_trim($resolved) !== '' ? $resolved : null;
    }

    /**
     * Resolve the country code for an address.
     *
     * With `$preferLiteral` the literal `country_code` wins over the referenced
     * country's ISO code; otherwise the referenced ISO code wins. Blank values
     * never win on either side.
     *
     * @param  array<string, mixed>  $address
     */
    public static function countryCode(array $address, bool $preferLiteral = false): ?string
    {
        if ($preferLiteral) {
            $countryCode = $address['country_code'] ?? null;

            if (is_string($countryCode) && mb_trim($countryCode) !== '') {
                return mb_trim($countryCode);
            }
        }

        $countryId = self::uuidValue($address['country_id'] ?? null);

        if ($countryId !== null) {
            $resolved = AddressCountry::query()->whereKey($countryId)->value('iso2');

            if (is_string($resolved) && mb_trim($resolved) !== '') {
                return mb_trim($resolved);
            }
        }

        if (! $preferLiteral) {
            $countryCode = $address['country_code'] ?? null;

            if (is_string($countryCode) && mb_trim($countryCode) !== '') {
                return mb_trim($countryCode);
            }
        }

        return null;
    }

    private static function slugSegment(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $segment = Str::slug($value);

        return $segment !== '' ? $segment : null;
    }

    private static function codeSegment(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $segment = Str::lower(mb_trim($value));

        return $segment !== '' ? $segment : null;
    }

    /**
     * @param  array<int, mixed>  $values
     */
    private static function firstFilled(array $values): ?string
    {
        foreach ($values as $value) {
            if (! is_string($value)) {
                continue;
            }

            $trimmed = mb_trim($value);

            if ($trimmed !== '') {
                return $trimmed;
            }
        }

        return null;
    }

    private static function uuidValue(mixed $value): ?string
    {
        return is_string($value) && Str::isUuid($value) ? $value : null;
    }
}
