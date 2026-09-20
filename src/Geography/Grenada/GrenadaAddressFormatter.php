<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Grenada;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;

final class GrenadaAddressFormatter implements CountryAddressFormatter
{
    public function countryCode(): string
    {
        return 'GD';
    }

    public function format(AddressData $address): string
    {
        $lines = array_filter([
            $address->line1,
            $address->line2,
            $address->line3,
        ]);

        // UPU: no postcode system; prints any supplied code on its own line.
        $city = self::textOrNull($address->city);
        $state = self::textOrNull($address->state);
        $postcode = self::textOrNull($address->postcode);

        if ($city !== null) {
            $lines[] = $city;
        }

        if ($state !== null && ($city === null || ! self::sameText($city, $state))) {
            $lines[] = $state;
        }

        if ($postcode !== null) {
            $lines[] = $postcode;
        }

        if ($address->country !== null && $address->country !== '') {
            $lines[] = $address->country;
        } elseif ($address->countryCode !== null && $address->countryCode !== '') {
            $lines[] = mb_strtoupper($address->countryCode) === 'GD' ? 'Grenada' : $address->countryCode;
        }

        return implode("\n", $lines);
    }

    private static function textOrNull(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = mb_trim($value);

        return $value === '' ? null : $value;
    }

    private static function sameText(string $a, string $b): bool
    {
        return mb_strtolower($a) === mb_strtolower($b);
    }
}
