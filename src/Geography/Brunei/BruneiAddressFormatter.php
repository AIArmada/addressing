<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Brunei;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class BruneiAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'BN';
    }

    public function format(AddressData $address): string
    {
        // UPU: town or district + postcode on one line, town preferred.
        return AddressFormatRenderer::format('BN', $address);
    }
}
