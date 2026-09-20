<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Uzbekistan;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;

final class UzbekistanAddressFormatter implements CountryAddressFormatter
{
    public function countryCode(): string
    {
        return 'UZ';
    }

    public function format(AddressData $address): string
    {
        $lines = array_filter([
            $address->line1,
            $address->line2,
            $address->line3,
        ]);

        // UPU: 6-digit postcode left of locality with a comma, region below.
        $city = self::textOrNull($address->city);
        $state = self::textOrNull($address->state);
        $postcode = self::textOrNull($address->postcode);

        if ($postcode !== null) {
            if ($city !== null) {
                $lines[] = $postcode . ', ' . $city;

                if ($state !== null && mb_strtolower($state) !== mb_strtolower($city)) {
                    $lines[] = $state;
                }
            } elseif ($state !== null) {
                $lines[] = $postcode . ', ' . $state;
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
            $lines[] = mb_strtoupper($address->countryCode) === 'UZ' ? 'Uzbekistan' : $address->countryCode;
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
