<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Taiwan;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class TaiwanAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'TW';
    }

    public function format(AddressData $address): string
    {
        // Chunghwa Post: 6-digit 3+3 postcode right of the locality name.
        return AddressFormatRenderer::format('TW', $address);
    }
}
