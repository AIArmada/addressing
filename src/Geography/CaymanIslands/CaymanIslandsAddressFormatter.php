<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\CaymanIslands;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class CaymanIslandsAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'KY';
    }

    public function format(AddressData $address): string
    {
        // UPU: KY + island digit + 4 digits right of the island with two spaces.
        return AddressFormatRenderer::format('KY', $address);
    }
}
