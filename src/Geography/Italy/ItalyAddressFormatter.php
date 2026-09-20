<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Italy;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;

final class ItalyAddressFormatter implements CountryAddressFormatter
{
    public function countryCode(): string
    {
        return 'IT';
    }

    public function format(AddressData $address): string
    {
        $lines = array_filter([
            $address->line1,
            $address->line2,
            $address->line3,
        ]);

        // UPU: 5-digit postcode left of locality plus province abbreviation via the province_code component.
        $city = self::textOrNull($address->city);
        $state = self::textOrNull($address->state);
        $postcode = self::textOrNull($address->postcode);

        $provinceCode = $this->component($address, 'province_code');

        if ($postcode !== null) {
            $locality = $city ?? $state;

            if ($locality !== null) {
                $line = $postcode . ' ' . $locality;

                if ($provinceCode !== null) {
                    $line .= ' ' . mb_strtoupper($provinceCode);
                }

                $lines[] = $line;
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
            $lines[] = mb_strtoupper($address->countryCode) === 'IT' ? 'Italy' : $address->countryCode;
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
