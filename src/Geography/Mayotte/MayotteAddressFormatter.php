<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Mayotte;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class MayotteAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'YT';
    }

    public function format(AddressData $address): string
    {
        // UPU: 976xx left of the locality (French system).
        return AddressFormatRenderer::format('YT', $address);
    }
}
