<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Algeria;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;

final class AlgeriaAddressFormatter implements CountryAddressFormatter
{
    public function countryCode(): string
    {
        return 'DZ';
    }

    public function format(AddressData $address): string
    {
        $lines = array_filter([
            $address->line1,
            $address->line2,
            $address->line3,
        ]);

        // UPU: 5-digit postcode to the left of the locality name.
        $city = self::textOrNull($address->city);
        $state = self::textOrNull($address->state);
        $postcode = self::textOrNull($address->postcode);

        if ($postcode !== null) {
            $locality = $city ?? $state;

            $lines[] = $locality !== null ? $postcode . ' ' . $locality : $postcode;
        } else {
            if ($city !== null) {
                $lines[] = $city;
            }

            if ($state !== null) {
                $lines[] = $state;
            }
        }

        if ($address->country !== null && $address->country !== '') {
            $lines[] = $address->country;
        } elseif ($address->countryCode !== null && $address->countryCode !== '') {
            $lines[] = mb_strtoupper($address->countryCode) === 'DZ' ? 'Algeria' : $address->countryCode;
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
