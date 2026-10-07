<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\UnitedStates;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class UnitedStatesAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'US';
    }

    public function format(AddressData $address): string
    {
        // USPS Publication 28: locality, state abbreviation, and ZIP on one line.
        return AddressFormatRenderer::format('US', $address);
    }
}
