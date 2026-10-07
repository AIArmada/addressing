<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Greece;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class GreeceAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'GR';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits as NNN NN left of the locality name.
        return AddressFormatRenderer::format('GR', $address);
    }
}
