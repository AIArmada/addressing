<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\IvoryCoast;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class IvoryCoastAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'CI';
    }

    public function format(AddressData $address): string
    {
        // UPU: no postcode system (2-digit office code is not a postcode); prints any supplied code on its own line.
        return AddressFormatRenderer::format('CI', $address);
    }
}
