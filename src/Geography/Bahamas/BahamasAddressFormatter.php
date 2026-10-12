<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Bahamas;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class BahamasAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'BS';
    }

    public function format(AddressData $address): string
    {
        // UPU BHS 10/2025: no postcode system; island joins the locality with a comma.
        return AddressFormatRenderer::format('BS', $address);
    }
}
