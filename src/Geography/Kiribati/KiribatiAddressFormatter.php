<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Kiribati;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class KiribatiAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'KI';
    }

    public function format(AddressData $address): string
    {
        // UPU: KI + 4 digits right of the island: STH TARAWA KI0107.
        return AddressFormatRenderer::format('KI', $address);
    }
}
