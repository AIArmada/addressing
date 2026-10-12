<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Nicaragua;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class NicaraguaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'NI';
    }

    public function format(AddressData $address): string
    {
        // UPU NIC: 5 digits own line above `municipality, department` (`Managua, Managua`).
        return AddressFormatRenderer::format('NI', $address);
    }
}
