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
        // SPL National Address: city line above the postcode (`RIYADH` / `13337`).
        return AddressFormatRenderer::format('SA', $address);
    }
}
