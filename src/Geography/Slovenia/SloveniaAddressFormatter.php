<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Slovenia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;

final class SloveniaAddressFormatter implements CountryAddressFormatter
{
    public function countryCode(): string
    {
        return 'SI';
    }

    public function format(AddressData $address): string
    {
        $lines = array_filter([
            $address->line1,
            $address->line2,
            $address->line3,
        ]);

        // UPU: 4 digits left of the locality (SI- prefix passes through as supplied).
        $city = self::textOrNull($address->city);
        $state = self::textOrNull($address->state);
        $postcode = self::textOrNull($address->postcode);

        if ($postcode !== null) {
            if ($city !== null) {
                $lines[] = $postcode . ' ' . $city;

                if ($state !== null && ! self::sameText($city, $state)) {
                    $lines[] = $state;
                }
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
            $lines[] = mb_strtoupper($address->countryCode) === 'SI' ? 'Slovenia' : $address->countryCode;
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
