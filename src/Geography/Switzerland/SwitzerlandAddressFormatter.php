<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Switzerland;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class SwitzerlandAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'CH';
    }

    public function format(AddressData $address): string
    {
        // UPU: 4 digits left of the town (office/canton suffixes pass through).
        return AddressFormatRenderer::format('CH', $address);
    }
}
