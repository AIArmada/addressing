<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Uzbekistan;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class UzbekistanAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'UZ';
    }

    public function format(AddressData $address): string
    {
        // UPU: 6-digit postcode left of locality with a comma, region below.
        return AddressFormatRenderer::format('UZ', $address);
    }
}
