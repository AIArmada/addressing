<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Belgium;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class BelgiumAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'BE';
    }

    public function format(AddressData $address): string
    {
        // UPU: 4 digits left of the locality (B-/BE- prefixes are forbidden).
        return AddressFormatRenderer::format('BE', $address);
    }
}
