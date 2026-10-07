<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\NorthMacedonia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class NorthMacedoniaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'MK';
    }

    public function format(AddressData $address): string
    {
        // UPU: 4 digits left of the post office name.
        return AddressFormatRenderer::format('MK', $address);
    }
}
