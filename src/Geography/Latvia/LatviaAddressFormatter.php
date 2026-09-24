<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Latvia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressLineFilter;

final class LatviaAddressFormatter implements CountryAddressFormatter
{
    public function countryCode(): string
    {
        return 'LV';
    }

    public function format(AddressData $address): string
    {
        $lines = AddressLineFilter::present([
            $address->line1,
            $address->line2,
            $address->line3,
        ]);

        // UPU: LV-NNNN right of the locality with a comma (prefix supplied as-is).
        $city = self::textOrNull($address->city);
        $state = self::textOrNull($address->state);
        $postcode = self::textOrNull($address->postcode);

        if ($postcode !== null) {
            if ($city !== null) {
                $lines[] = $city . ', ' . $postcode;

                if ($state !== null && ! self::sameText($city, $state)) {
                    $lines[] = $state;
                }
            } elseif ($state !== null) {
                $lines[] = $state . ', ' . $postcode;
            } else {
                $lines[] = $postcode;
            }
        } else {
            if ($city !== null) {
                $lines[] = $city;
            }

            if ($state !== null && ($city === null || ! self::sameText($city, $state))) {
                $lines[] = $state;
            }
        }

        if ($address->country !== null && $address->country !== '') {
            $lines[] = $address->country;
        } elseif ($address->countryCode !== null && $address->countryCode !== '') {
            $lines[] = mb_strtoupper($address->countryCode) === 'LV' ? 'Latvia' : $address->countryCode;
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
