<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Poland;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class PolandAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'PL';
    }

    public function format(AddressData $address): string
    {
        // UPU: NN-NNN postcode to the left of the locality name.
        return AddressFormatRenderer::format('PL', $address);
    }
}
