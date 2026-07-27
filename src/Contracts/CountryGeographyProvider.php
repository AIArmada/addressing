<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

use AIArmada\Addressing\Models\AddressCountry;

/**
 * Supplies country-specific geographic reference data to the generic addressing package.
 */
interface CountryGeographyProvider extends CountryAddressProfile
{
    /**
     * Stable ownership key for provider-managed data.
     *
     * This must not change when a provider changes the feed/source key it imports from.
     */
    public function providerKey(): string;

    public function countryCode(): string;

    public function seed(AddressCountry $country): void;
}
