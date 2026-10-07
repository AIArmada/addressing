<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Netherlands;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class NetherlandsAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'NL';
    }

    public function format(AddressData $address): string
    {
        // UPU: NNNN LL postcode left of locality with two spaces between.
        return AddressFormatRenderer::format('NL', $address);
    }
}
