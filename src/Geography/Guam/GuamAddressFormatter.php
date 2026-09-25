<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Guam;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressLineFilter;

final class GuamAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'GU';
    }

    public function format(AddressData $address): string
    {
        $lines = AddressLineFilter::present([
            $address->line1,
            $address->line2,
            $address->line3,
        ]);

        // UPU: US ZIP on the locality line: BARRIGADA GU 96913.
        $city = self::textOrNull($address->city);
        $state = self::textOrNull($address->state);
        $postcode = self::textOrNull($address->postcode);

        if ($state !== null && self::sameText($state, 'GU')) {
            $state = null;
        }

        if ($city !== null && $state !== null && ! self::sameText($city, $state)) {
            $lines[] = $city;
            $lines[] = $state . ' GU' . ($postcode !== null ? ' ' . $postcode : '');
        } elseif ($city !== null) {
            $lines[] = $city . ' GU' . ($postcode !== null ? ' ' . $postcode : '');
        } elseif ($state !== null) {
            $lines[] = $state . ' GU' . ($postcode !== null ? ' ' . $postcode : '');
        } elseif ($postcode !== null) {
            $lines[] = $postcode;
        }

        if ($address->country !== null && $address->country !== '') {
            $lines[] = $address->country;
        } elseif ($address->countryCode !== null && $address->countryCode !== '') {
            $lines[] = mb_strtoupper($address->countryCode) === 'GU' ? 'Guam' : $address->countryCode;
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
