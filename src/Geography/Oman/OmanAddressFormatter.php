<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Oman;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class OmanAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'OM';
    }

    public function format(AddressData $address): string
    {
        // UPU: 3-digit postcode above the locality name.
        return AddressFormatRenderer::format('OM', $address);
    }
}
