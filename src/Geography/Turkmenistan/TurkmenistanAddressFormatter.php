<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Turkmenistan;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class TurkmenistanAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'TM';
    }

    public function format(AddressData $address): string
    {
        // UPU: 6 digits on their own line below the locality.
        return AddressFormatRenderer::format('TM', $address);
    }
}
