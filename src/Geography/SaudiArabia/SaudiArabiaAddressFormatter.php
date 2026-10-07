<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\SaudiArabia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class SaudiArabiaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'SA';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5-digit postcode above the locality name.
        return AddressFormatRenderer::format('SA', $address);
    }
}
