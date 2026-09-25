<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Indonesia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressLineFilter;

final class IndonesiaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'ID';
    }

    public function format(AddressData $address): string
    {
        $lines = AddressLineFilter::present([
            $address->line1,
            $address->line2,
            $address->line3,
            $this->component($address, 'kelurahan'),
            $this->component($address, 'desa'),
            $this->component($address, 'kecamatan'),
        ]);

        if ($address->city !== null && $address->city !== '') {
            $city = $address->city;

            if ($address->postcode !== null && $address->postcode !== '') {
                $city .= ' ' . $address->postcode;
            }

            $lines[] = $city;
        } elseif ($address->postcode !== null && $address->postcode !== '') {
            $lines[] = $address->postcode;
        }

        if ($address->state !== null && $address->state !== '') {
            $lines[] = $address->state;
        }

        if ($address->country !== null && $address->country !== '') {
            $lines[] = $address->country;
        } elseif ($address->countryCode !== null && $address->countryCode !== '') {
            $lines[] = mb_strtoupper($address->countryCode) === 'ID' ? 'Indonesia' : $address->countryCode;
        }

        return implode("\n", $lines);
    }

    private function component(AddressData $address, string $key): ?string
    {
        $value = $address->components[$key] ?? null;

        if (! is_scalar($value)) {
            return null;
        }

        $value = mb_trim((string) $value);

        return $value === '' ? null : $value;
    }
}
