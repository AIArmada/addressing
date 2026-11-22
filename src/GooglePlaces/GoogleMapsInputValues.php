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

        return Str::after($normalized, 'places/');
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
            return (float) $value;
        }

        if (! is_string($value)) {
            return null;
        }

        $trimmed = mb_trim($value);

        return is_numeric($trimmed) ? (float) $trimmed : null;
    }
}
