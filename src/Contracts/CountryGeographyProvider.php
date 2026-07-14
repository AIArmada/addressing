<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

use AIArmada\Addressing\Models\AddressCountry;

/**
 * Supplies country-specific State/City data to the generic addressing package.
 */
interface CountryGeographyProvider extends CountryAddressProfile
{
    public function countryCode(): string;

    public function seed(AddressCountry $country): void;
}
