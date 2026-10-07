<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Slovakia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class SlovakiaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'SK';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits as XXX XX left of the delivery office.
        return AddressFormatRenderer::format('SK', $address);
    }
}
