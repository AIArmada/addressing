<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Cambodia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class CambodiaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'KH';
    }

    public function format(AddressData $address): string
    {
        // UPU: 6 digits right of the locality or province name.
        return AddressFormatRenderer::format('KH', $address);
    }
}
