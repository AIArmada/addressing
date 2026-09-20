<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Turkiye;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;

final class TurkiyeAddressFormatter implements CountryAddressFormatter
{
    public function countryCode(): string
    {
        return 'TR';
    }

    public function format(AddressData $address): string
    {
        $lines = array_filter([
            $address->line1,
            $address->line2,
            $address->line3,
        ]);

        // UPU: 5-digit postcode left of locality/province (06050-01 sub-locality numbers unsupported).
        $city = self::textOrNull($address->city);
        $state = self::textOrNull($address->state);
        $postcode = self::textOrNull($address->postcode);

        if ($postcode !== null) {
            if ($city !== null && $state !== null) {
                $lines[] = $postcode . ' ' . $city . '/' . $state;
            } elseif ($city !== null) {
                $lines[] = $postcode . ' ' . $city;
            } elseif ($state !== null) {
                $lines[] = $postcode . ' ' . $state;
            } else {
                $lines[] = $postcode;
            }
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
            $lines[] = mb_strtoupper($address->countryCode) === 'TR' ? 'Türkiye' : $address->countryCode;
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
