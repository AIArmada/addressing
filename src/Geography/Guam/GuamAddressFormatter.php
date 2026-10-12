<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Guam;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class GuamAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'GU';
    }

    public function format(AddressData $address): string
    {
        // USPS Pub 28 s224 + UPU GUM: village detail above; last line `CITY GU ZIP`.
        return AddressFormatRenderer::format('GU', $address);
    }
}
