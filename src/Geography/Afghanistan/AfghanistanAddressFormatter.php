<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Afghanistan;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;

final class AfghanistanAddressFormatter implements CountryAddressFormatter
{
    public function countryCode(): string
    {
        return 'AF';
    }

    public function format(AddressData $address): string
    {
        $lines = array_filter([
            $address->line1,
            $address->line2,
            $address->line3,
        ]);

        // UPU: locality on its own line, then the 6-digit postcode left
        // of the province (`100208 KABUL` in every example; new system
        // from 1 October 2024, province-encoded).
        $city = self::textOrNull($address->city);
        $state = self::textOrNull($address->state);
        $postcode = self::textOrNull($address->postcode);

        if ($city !== null) {
            $lines[] = $city;
        }

        if ($postcode !== null && $state !== null) {
            $lines[] = $postcode . ' ' . $state;
        } elseif ($state !== null) {
            $lines[] = $state;
        } elseif ($postcode !== null) {
            $lines[] = $postcode;
        }

        if ($address->country !== null && $address->country !== '') {
            $lines[] = $address->country;
        } elseif ($address->countryCode !== null && $address->countryCode !== '') {
            $lines[] = mb_strtoupper($address->countryCode) === 'AF' ? 'Afghanistan' : $address->countryCode;
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
