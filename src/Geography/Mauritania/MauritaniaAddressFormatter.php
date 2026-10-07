<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Mauritania;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class MauritaniaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'MR';
    }

    public function format(AddressData $address): string
    {
        // UPU: no postcode system; prints any supplied code on its own line.
        return AddressFormatRenderer::format('MR', $address);
    }
}
