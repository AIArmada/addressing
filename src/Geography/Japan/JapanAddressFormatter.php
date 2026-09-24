<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Japan;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressLineFilter;

final class JapanAddressFormatter implements CountryAddressFormatter
{
    public function countryCode(): string
    {
        return 'JP';
    }

    public function format(AddressData $address): string
    {
        $lines = AddressLineFilter::present([
            $address->line1,
            $address->line2,
            $address->line3,
        ]);

        // UPU western layout: locality and prefecture share one line and
        // the NNN-NNNN postcode shares the last line with the country
        // (`231-0012 JAPAN` in all three detailed UPU examples).
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

        if ($address->country !== null && $address->country !== '') {
            $country = $address->country;
        } elseif ($address->countryCode !== null && $address->countryCode !== '') {
            $country = mb_strtoupper($address->countryCode) === 'JP' ? 'Japan' : $address->countryCode;
        } else {
            $country = null;
        }

        if ($postcode !== null && $country !== null) {
            $lines[] = $postcode . ' ' . $country;
        } elseif ($postcode !== null) {
            $lines[] = $postcode;
        } elseif ($country !== null) {
            $lines[] = $country;
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
