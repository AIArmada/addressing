<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Iraq;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class IraqAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'IQ';
    }

    public function format(AddressData $address): string
    {
        // UPU: city and governorate, then 5-digit postcode below.
        return AddressFormatRenderer::format('IQ', $address);
    }
}
