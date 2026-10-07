<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Cuba;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class CubaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'CU';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits with CP prefix left of the locality (prefix supplied as-is).
        return AddressFormatRenderer::format('CU', $address);
    }
}
