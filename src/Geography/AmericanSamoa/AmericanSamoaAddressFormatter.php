<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\AmericanSamoa;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class AmericanSamoaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'AS';
    }

    public function format(AddressData $address): string
    {
        // USPS Pub 28 s224 + UPU ASM: district detail above; last line `CITY AS ZIP`.
        return AddressFormatRenderer::format('AS', $address);
    }
}
