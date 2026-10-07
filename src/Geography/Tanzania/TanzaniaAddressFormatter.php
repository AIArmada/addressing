<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Tanzania;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class TanzaniaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'TZ';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5-digit postcode left of locality, region on its own line.
        return AddressFormatRenderer::format('TZ', $address);
    }
}
