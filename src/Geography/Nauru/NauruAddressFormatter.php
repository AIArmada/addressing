<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Nauru;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class NauruAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'NR';
    }

    public function format(AddressData $address): string
    {
        // UPU: sole postcode NRU68 on its own line after the district.
        return AddressFormatRenderer::format('NR', $address);
    }
}
