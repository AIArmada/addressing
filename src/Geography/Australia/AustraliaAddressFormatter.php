<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Australia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class AustraliaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'AU';
    }

    public function format(AddressData $address): string
    {
        // UPU: locality, state abbreviation, and postcode separated by two spaces.
        return AddressFormatRenderer::format('AU', $address);
    }
}
