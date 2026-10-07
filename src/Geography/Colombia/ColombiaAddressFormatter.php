<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Colombia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class ColombiaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'CO';
    }

    public function format(AddressData $address): string
    {
        // UPU: 6-digit postcode right of locality, department on its own line.
        return AddressFormatRenderer::format('CO', $address);
    }
}
