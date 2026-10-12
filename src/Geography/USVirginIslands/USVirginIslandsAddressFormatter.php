<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\USVirginIslands;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class USVirginIslandsAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'VI';
    }

    public function format(AddressData $address): string
    {
        // USPS Pub 28 s224 + UPU VIR: island detail above; last line `CITY VI ZIP`.
        return AddressFormatRenderer::format('VI', $address);
    }
}
