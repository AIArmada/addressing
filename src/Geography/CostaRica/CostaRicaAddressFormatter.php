<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\CostaRica;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class CostaRicaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'CR';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits on their own line above the country name.
        return AddressFormatRenderer::format('CR', $address);
    }
}
