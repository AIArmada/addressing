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
        // UPU CRI: province, canton, district comma-joined on one line; 5 digits own line above country.
        return AddressFormatRenderer::format('CR', $address);
    }
}
