<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Malaysia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;

final class MalaysiaAddressFormatter implements CountryAddressFormatter
{
    public function countryCode(): string
    {
        return 'MY';
    }

    public function format(AddressData $address): string
    {
        $lines = array_filter([
            $address->line1,
            $address->line2,
            $address->line3,
            $this->component($address, 'mukim'),
            $this->component($address, 'subdistrict'),
            $this->component($address, 'district'),
        ]);

        $cityLine = array_filter([$address->city]);

        if ($cityLine !== []) {
            $city = implode(', ', $cityLine);

            if ($address->postcode !== null && $address->postcode !== '') {
                $city = $address->postcode . ' ' . $city;
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
            $lines[] = $address->countryCode;
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
