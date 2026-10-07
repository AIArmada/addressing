<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Tunisia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class TunisiaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'TN';
    }

    public function format(AddressData $address): string
    {
        // UPU: 4 digits left of the locality name.
        return AddressFormatRenderer::format('TN', $address);
    }
}
