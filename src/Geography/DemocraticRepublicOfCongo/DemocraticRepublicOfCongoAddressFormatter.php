<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\DemocraticRepublicOfCongo;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class DemocraticRepublicOfCongoAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'CD';
    }

    public function format(AddressData $address): string
    {
        // UPU: 7-digit postcode left of the province name.
        return AddressFormatRenderer::format('CD', $address);
    }
}
