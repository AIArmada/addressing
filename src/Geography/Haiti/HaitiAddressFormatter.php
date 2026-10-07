<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Haiti;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class HaitiAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'HT';
    }

    public function format(AddressData $address): string
    {
        // UPU: HT + 4 digits, integral even domestically, left of the locality.
        return AddressFormatRenderer::format('HT', $address);
    }
}
