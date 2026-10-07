<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\SouthKorea;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class SouthKoreaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'KR';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5-digit postcode right of the province/city name.
        return AddressFormatRenderer::format('KR', $address);
    }
}
