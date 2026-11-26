<?php

declare(strict_types=1);

namespace AIArmada\Addressing\GooglePlaces;

use Illuminate\Support\Str;

final class GoogleMapsInputValues
{
    public static function string(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = mb_trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    public static function placeId(mixed $value): ?string
    {
        $normalized = self::string($value);

        if ($normalized === null) {
            return null;
        }

        $id = Str::after($normalized, 'places/');

        return $id !== '' && mb_strlen($id) <= 255 && preg_match('~^[A-Za-z0-9_\\-]+$~D', $id) === 1 ? $id : null;
    }

    public static function displayName(mixed $value): ?string
    {
        if (is_array($value)) {
            $text = $value['text'] ?? null;

            return self::string(is_string($text) ? $text : null);
        }

        return self::string($value);
    }

    public static function number(mixed $value): ?float
    {
        if (is_float($value) || is_int($value)) {
            return is_finite((float) $value) ? (float) $value : null;
        }

        if (! is_string($value)) {
            return null;
        }

        $trimmed = mb_trim($value);

        return is_numeric($trimmed) && is_finite((float) $trimmed) ? (float) $trimmed : null;
    }

    public static function latitude(mixed $value): ?float
    {
        $number = self::number($value);

        return $number !== null && abs($number) <= 90 ? $number : null;
    }

    public static function longitude(mixed $value): ?float
    {
        $number = self::number($value);

        return $number !== null && abs($number) <= 180 ? $number : null;
    }
}
