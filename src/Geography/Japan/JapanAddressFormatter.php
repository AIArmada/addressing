<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Japan;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;

final class JapanAddressFormatter implements CountryAddressFormatter
{
    public function countryCode(): string
    {
        return 'JP';
    }

    public function format(AddressData $address): string
    {
        $lines = array_filter([
            $address->line1,
            $address->line2,
            $address->line3,
        ]);

        // UPU western layout: city and prefecture, then NNN-NNNN postcode below.
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
            $lines[] = mb_strtoupper($address->countryCode) === 'JP' ? 'Japan' : $address->countryCode;
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
