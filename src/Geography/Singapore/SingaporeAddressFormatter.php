<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Singapore;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressLineFilter;

final class SingaporeAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'SG';
    }

    public function format(AddressData $address): string
    {
        $lines = AddressLineFilter::present([
            $address->line1,
            $address->line2,
            $address->line3,
        ]);

        $city = $this->distinctive($address->city);

        if ($city !== null) {
            $lines[] = $city;
        }

        $state = $this->distinctive($address->state);

        if ($state !== null) {
            $lines[] = $state;
        }

        $country = $address->country;

        if ($country === null || $country === '') {
            $country = $address->countryCode !== null && mb_strtoupper($address->countryCode) === 'SG'
                ? 'Singapore'
                : $address->countryCode;
        }

        if ($address->postcode !== null && $address->postcode !== '') {
            $lines[] = $country !== null && $country !== ''
                ? $country . ' ' . $address->postcode
                : $address->postcode;
        } elseif ($country !== null && $country !== '') {
            $lines[] = $country;
        }

        return implode("\n", $lines);
    }

    private function distinctive(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = mb_trim($value);

        if ($value === '' || mb_strtolower($value) === 'singapore') {
            return null;
        }

        return $value;
    }
}
