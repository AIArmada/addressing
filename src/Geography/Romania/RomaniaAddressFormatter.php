<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Romania;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class RomaniaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'RO';
    }

    public function format(AddressData $address): string
    {
        // UPU: 6 digits left of the locality name.
        return AddressFormatRenderer::format('RO', $address);
    }
}
