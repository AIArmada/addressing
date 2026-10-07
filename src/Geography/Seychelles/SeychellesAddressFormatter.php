<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Seychelles;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class SeychellesAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'SC';
    }

    public function format(AddressData $address): string
    {
        // UPU: no postcode system; prints any supplied code on its own line.
        return AddressFormatRenderer::format('SC', $address);
    }
}
