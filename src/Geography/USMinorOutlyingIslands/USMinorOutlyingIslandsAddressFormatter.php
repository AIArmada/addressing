<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\USMinorOutlyingIslands;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class USMinorOutlyingIslandsAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'UM';
    }

    public function format(AddressData $address): string
    {
        // USPS Pub 28 s22/App B: single last line `CITY ST ZIP` (Wake Island 96898).
        return AddressFormatRenderer::format('UM', $address);
    }
}
