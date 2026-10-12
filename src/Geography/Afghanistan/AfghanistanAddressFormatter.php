<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Afghanistan;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class AfghanistanAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'AF';
    }

    public function format(AddressData $address): string
    {
        // UPU AFG 07/2025: 6-digit postcode joins the district/city; province
        // on its own line (urban `100208 KABUL`, rural `106401 PAGHMAN / KABUL`).
        return AddressFormatRenderer::format('AF', $address);
    }
}
