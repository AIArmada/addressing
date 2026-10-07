<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Finland;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class FinlandAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'FI';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits left of the locality (optional FI- passes through).
        return AddressFormatRenderer::format('FI', $address);
    }
}
