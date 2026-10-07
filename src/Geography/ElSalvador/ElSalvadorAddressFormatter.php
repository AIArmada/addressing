<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\ElSalvador;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class ElSalvadorAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'SV';
    }

    public function format(AddressData $address): string
    {
        // UPU: 4 digits left of the locality name.
        return AddressFormatRenderer::format('SV', $address);
    }
}
