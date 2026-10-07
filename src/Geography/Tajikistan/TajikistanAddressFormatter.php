<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Tajikistan;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class TajikistanAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'TJ';
    }

    public function format(AddressData $address): string
    {
        // UPU: 6 digits left of the locality name.
        return AddressFormatRenderer::format('TJ', $address);
    }
}
