<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Contracts\AddressFormatter;
use AIArmada\Addressing\Data\AddressData;

class FormatAddressAction implements AddressFormatter
{
    public function format(AddressData $address): string
    {
        $lines = array_filter([
            $address->line1,
            $address->line2,
            $address->line3,
        ]);

        $cityLine = array_filter([
            $address->city,
            $address->state,
        ]);

        if ($cityLine !== []) {
            $lines[] = implode(', ', $cityLine);
        }

        if ($address->postcode !== null && $address->postcode !== '') {
            if ($cityLine !== []) {
                $lines[count($lines) - 1] = $address->postcode . ' ' . end($lines);
            } else {
                $lines[] = $address->postcode;
            }
        }

        if ($address->country !== null && $address->country !== '') {
            $lines[] = $address->country;
        } elseif ($address->countryCode !== null && $address->countryCode !== '') {
            $lines[] = $address->countryCode;
        }

        return implode("\n", $lines);
    }
}
