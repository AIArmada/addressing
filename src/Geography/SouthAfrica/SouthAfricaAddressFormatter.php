<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\SouthAfrica;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class SouthAfricaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'ZA';
    }

    public function format(AddressData $address): string
    {
        // UPU: locality then 4-digit postcode below; province only without postcode.
        return AddressFormatRenderer::format('ZA', $address);
    }
}
