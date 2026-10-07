<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Venezuela;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class VenezuelaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'VE';
    }

    public function format(AddressData $address): string
    {
        // UPU: 4 digits right of the locality (extended NNNN-A passes through).
        return AddressFormatRenderer::format('VE', $address);
    }
}
