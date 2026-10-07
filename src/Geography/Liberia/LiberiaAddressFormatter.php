<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Liberia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class LiberiaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'LR';
    }

    public function format(AddressData $address): string
    {
        // UPU: 4 digits left of the locality (Monrovia zone suffix passes through as supplied).
        return AddressFormatRenderer::format('LR', $address);
    }
}
