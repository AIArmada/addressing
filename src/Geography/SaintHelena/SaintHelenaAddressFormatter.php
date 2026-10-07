<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\SaintHelena;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class SaintHelenaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'SH';
    }

    public function format(AddressData $address): string
    {
        // UPU: UK-style code right of the locality: JAMESTOWN STHL 1ZZ.
        return AddressFormatRenderer::format('SH', $address);
    }
}
