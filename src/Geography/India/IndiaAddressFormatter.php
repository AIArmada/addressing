<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\India;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class IndiaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'IN';
    }

    public function format(AddressData $address): string
    {
        // UPU: locality, state, then 6-digit postcode below (secondary postcodes unsupported).
        return AddressFormatRenderer::format('IN', $address);
    }
}
