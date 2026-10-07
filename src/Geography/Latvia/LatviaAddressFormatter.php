<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Latvia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class LatviaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'LV';
    }

    public function format(AddressData $address): string
    {
        // UPU: LV-NNNN right of the locality with a comma (prefix supplied as-is).
        return AddressFormatRenderer::format('LV', $address);
    }
}
