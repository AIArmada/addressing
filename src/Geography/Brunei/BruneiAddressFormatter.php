<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Brunei;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressLineFilter;

final class BruneiAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'BN';
    }

    public function format(AddressData $address): string
    {
        $lines = AddressLineFilter::present([
            $address->line1,
            $address->line2,
            $address->line3,
            $this->component($address, 'kampung'),
            $this->component($address, 'kampong'),
            $this->component($address, 'mukim'),
        ]);

        // UPU: town or district + postcode on one line, town preferred.
        $city = self::textOrNull($address->city);
        $state = self::textOrNull($address->state);
        $postcode = self::textOrNull($address->postcode);

        if ($postcode !== null) {
            $locality = $city ?? $state;

            $lines[] = $locality !== null ? $locality . ' ' . $postcode : $postcode;
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
            $lines[] = mb_strtoupper($address->countryCode) === 'BN' ? 'Brunei Darussalam' : $address->countryCode;
        }

        return implode("\n", $lines);
    }

    private function component(AddressData $address, string $key): ?string
    {
        $value = $address->components[$key] ?? null;

        if (! is_scalar($value)) {
            return null;
        }

        return self::textOrNull((string) $value);
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
