<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Canada;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class CanadaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'CA';
    }

    public function format(AddressData $address): string
    {
        // UPU: locality, province abbreviation, and ANA NAN postcode on one line.
        return AddressFormatRenderer::format('CA', $address);
    }
}
