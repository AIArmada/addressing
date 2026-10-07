<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Serbia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class SerbiaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'RS';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits left of the delivery office (street-level PAK has no field).
        return AddressFormatRenderer::format('RS', $address);
    }
}
