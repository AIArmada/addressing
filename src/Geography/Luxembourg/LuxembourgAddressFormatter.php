<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Luxembourg;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class LuxembourgAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'LU';
    }

    public function format(AddressData $address): string
    {
        // UPU: L-NNNN left of the locality (prefix supplied as-is).
        return AddressFormatRenderer::format('LU', $address);
    }
}
