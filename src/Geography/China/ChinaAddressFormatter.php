<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\China;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class ChinaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'CN';
    }

    public function format(AddressData $address): string
    {
        // UPU: 6-digit postcode left of the province name, with the
        // district/county/city (sub-province) on its own line above.
        return AddressFormatRenderer::format('CN', $address);
    }
}
