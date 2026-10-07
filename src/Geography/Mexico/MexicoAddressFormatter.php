<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Mexico;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class MexicoAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'MX';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5-digit postcode left of locality, state abbreviation after.
        return AddressFormatRenderer::format('MX', $address);
    }
}
