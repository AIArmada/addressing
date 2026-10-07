<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\NewCaledonia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class NewCaledoniaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'NC';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits (988xx) left of the locality (French system).
        return AddressFormatRenderer::format('NC', $address);
    }
}
