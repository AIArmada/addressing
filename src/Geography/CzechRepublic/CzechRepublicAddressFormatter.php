<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\CzechRepublic;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class CzechRepublicAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'CZ';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits as NNN NN left of the locality (Prague district suffix passes through).
        return AddressFormatRenderer::format('CZ', $address);
    }
}
