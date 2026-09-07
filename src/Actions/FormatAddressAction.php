<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Contracts\AddressFormatter;
use AIArmada\Addressing\Contracts\AddressNormalizer;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\CountryAddressFormatterResolver;

class FormatAddressAction implements AddressFormatter
{
    public function __construct(
        private readonly CountryAddressFormatterResolver $countryFormatters,
        private readonly AddressNormalizer $normalizer,
    ) {}

    public function format(AddressData $address): string
    {
        $address = $this->normalizer->normalize($address->toArray());
        $countryFormatter = $this->countryFormatters->resolve($address->countryCode);

        if ($countryFormatter !== null && $countryFormatter !== $this) {
            return $countryFormatter->format($address);
        }

        return $this->formatGeneric($address);
    }

    private function formatGeneric(AddressData $address): string
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
