<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Iceland;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class IcelandAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'IS';
    }

    public function format(AddressData $address): string
    {
        // UPU: 3 digits left of the locality name.
        return AddressFormatRenderer::format('IS', $address);
    }
}
