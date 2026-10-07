<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Aruba;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class ArubaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'AW';
    }

    public function format(AddressData $address): string
    {
        // UPU: no postcode system; prints any supplied code on its own line.
        return AddressFormatRenderer::format('AW', $address);
    }
}
