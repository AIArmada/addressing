<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Brazil;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class BrazilAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'BR';
    }

    public function format(AddressData $address): string
    {
        // UPU: locality and state abbreviation, 8-digit NNNNN-NNN postcode below.
        return AddressFormatRenderer::format('BR', $address);
    }
}
