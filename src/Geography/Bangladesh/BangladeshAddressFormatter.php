<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Bangladesh;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;

final class BangladeshAddressFormatter implements CountryAddressFormatter
{
    public function countryCode(): string
    {
        return 'BD';
    }

    public function format(AddressData $address): string
    {
        $lines = array_filter([
            $address->line1,
            $address->line2,
            $address->line3,
            $this->component($address, 'thana'),
        ]);

        // UPU: 4-digit postcode right of locality, dash-separated; thana optional.
        $city = self::textOrNull($address->city);
        $state = self::textOrNull($address->state);
        $postcode = self::textOrNull($address->postcode);

        if ($postcode !== null) {
            $locality = $city ?? $state;

            $lines[] = $locality !== null ? $locality . ' - ' . $postcode : $postcode;
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
            $lines[] = mb_strtoupper($address->countryCode) === 'BD' ? 'Bangladesh' : $address->countryCode;
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
