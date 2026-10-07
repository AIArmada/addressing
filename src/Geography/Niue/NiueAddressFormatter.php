<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Niue;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class NiueAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'NU';
    }

    public function format(AddressData $address): string
    {
        // UPU: sole postcode 9974 right of the locality.
        return AddressFormatRenderer::format('NU', $address);
    }
}
