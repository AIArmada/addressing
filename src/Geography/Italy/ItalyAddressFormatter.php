<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Italy;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class ItalyAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'IT';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5-digit postcode left of locality plus province abbreviation via the province_code component.
        return AddressFormatRenderer::format('IT', $address);
    }
}
