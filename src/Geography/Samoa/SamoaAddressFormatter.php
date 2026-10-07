<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Samoa;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class SamoaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'WS';
    }

    public function format(AddressData $address): string
    {
        // UPU: WS + 4 digits right of the locality: APIA WS1330.
        return AddressFormatRenderer::format('WS', $address);
    }
}
