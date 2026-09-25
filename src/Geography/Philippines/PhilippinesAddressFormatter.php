<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Philippines;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressLineFilter;

final class PhilippinesAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'PH';
    }

    public function format(AddressData $address): string
    {
        $lines = AddressLineFilter::present([
            $address->line1,
            $address->line2,
            $address->line3,
        ]);

        // UPU: 4-digit postcode left of the province name, with the
        // municipality on its own line above. Metro Manila instead joins
        // municipality and region on the postcode line.
        $city = self::textOrNull($address->city);
        $state = self::textOrNull($address->state);
        $postcode = self::textOrNull($address->postcode);

        if ($postcode !== null) {
            if ($state !== null) {
                if ($city !== null && ! self::sameText($city, $state)) {
                    if (self::isMetroManila($state)) {
                        $lines[] = $postcode . ' ' . $city . ', ' . $state;
                    } else {
                        $lines[] = $city;
                        $lines[] = $postcode . ' ' . $state;
                    }
                } else {
                    $lines[] = $postcode . ' ' . $state;
                }
            } elseif ($city !== null) {
                $lines[] = $postcode . ' ' . $city;
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
            $lines[] = mb_strtoupper($address->countryCode) === 'PH' ? 'Philippines' : $address->countryCode;
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

    private static function isMetroManila(string $state): bool
    {
        return in_array(mb_strtolower($state), [
            'metro manila',
            'national capital region',
            'national capital region (metro manila)',
            'ncr',
        ], true);
    }
}
