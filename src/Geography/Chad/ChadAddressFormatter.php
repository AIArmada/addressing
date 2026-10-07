<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Chad;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class ChadAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'TD';
    }

    public function format(AddressData $address): string
    {
        // UPU: no postcode system; prints any supplied code on its own line.
        return AddressFormatRenderer::format('TD', $address);
    }
}
