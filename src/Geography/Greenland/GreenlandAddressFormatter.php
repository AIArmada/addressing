<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Greenland;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class GreenlandAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'GL';
    }

    public function format(AddressData $address): string
    {
        // UPU: 4 digits (39xx) left of the locality (Danish system).
        return AddressFormatRenderer::format('GL', $address);
    }
}
