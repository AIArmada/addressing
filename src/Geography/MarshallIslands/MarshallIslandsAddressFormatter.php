<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\MarshallIslands;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class MarshallIslandsAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'MH';
    }

    public function format(AddressData $address): string
    {
        // USPS Pub 28 s224 + UPU MHL: atoll detail above; last line `CITY MH ZIP`.
        return AddressFormatRenderer::format('MH', $address);
    }
}
