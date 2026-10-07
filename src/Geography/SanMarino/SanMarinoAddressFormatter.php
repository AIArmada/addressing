<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\SanMarino;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class SanMarinoAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'SM';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits (47890-47899) left of the locality name.
        return AddressFormatRenderer::format('SM', $address);
    }
}
