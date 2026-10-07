<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Somalia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class SomaliaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'SO';
    }

    public function format(AddressData $address): string
    {
        // UPU: paper-only AA NNNNN never operational; prints any supplied code on its own line.
        return AddressFormatRenderer::format('SO', $address);
    }
}
