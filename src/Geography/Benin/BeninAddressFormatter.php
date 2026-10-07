<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Benin;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class BeninAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'BJ';
    }

    public function format(AddressData $address): string
    {
        // UPU: no postcode system (2-digit BP prefix is office routing); prints any supplied code on its own line.
        return AddressFormatRenderer::format('BJ', $address);
    }
}
