<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Portugal;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class PortugalAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'PT';
    }

    public function format(AddressData $address): string
    {
        // UPU: 7 digits as NNNN-NNN left of the locality name.
        return AddressFormatRenderer::format('PT', $address);
    }
}
