<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Iraq;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;

final class IraqAddressFormatter implements CountryAddressFormatter
{
    public function countryCode(): string
    {
        return 'IQ';
    }

    public function format(AddressData $address): string
    {
        $lines = array_filter([
            $address->line1,
            $address->line2,
            $address->line3,
        ]);

        // UPU: city and governorate, then 5-digit postcode below.
        $city = self::textOrNull($address->city);
        $state = self::textOrNull($address->state);
        $postcode = self::textOrNull($address->postcode);

        if ($city !== null && $state !== null) {
            $lines[] = $city . ', ' . $state;
        } elseif ($city !== null) {
            $lines[] = $city;
        } elseif ($state !== null) {
            $lines[] = $state;
        }

        if ($postcode !== null) {
            $lines[] = $postcode;
        }

        if ($address->country !== null && $address->country !== '') {
            $lines[] = $address->country;
        } elseif ($address->countryCode !== null && $address->countryCode !== '') {
            $lines[] = mb_strtoupper($address->countryCode) === 'IQ' ? 'Iraq' : $address->countryCode;
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
