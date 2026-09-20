<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\SaudiArabia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;

final class SaudiArabiaAddressFormatter implements CountryAddressFormatter
{
    public function countryCode(): string
    {
        return 'SA';
    }

    public function format(AddressData $address): string
    {
        $lines = array_filter([
            $address->line1,
            $address->line2,
            $address->line3,
        ]);

        // UPU: 5-digit postcode above the locality name.
        $city = self::textOrNull($address->city);
        $state = self::textOrNull($address->state);
        $postcode = self::textOrNull($address->postcode);

        if ($postcode !== null) {
            $lines[] = $postcode;
        }

        $locality = $city ?? $state;

        if ($locality !== null) {
            $lines[] = $locality;
        }

        if ($address->country !== null && $address->country !== '') {
            $lines[] = $address->country;
        } elseif ($address->countryCode !== null && $address->countryCode !== '') {
            $lines[] = mb_strtoupper($address->countryCode) === 'SA' ? 'Saudi Arabia' : $address->countryCode;
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
}
