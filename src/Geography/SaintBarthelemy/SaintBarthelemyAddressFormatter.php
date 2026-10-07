<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\SaintBarthelemy;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class SaintBarthelemyAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'BL';
    }

    public function format(AddressData $address): string
    {
        // UPU: single 97133 code left of the locality (French system).
        return AddressFormatRenderer::format('BL', $address);
    }
}
