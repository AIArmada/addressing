<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\NewZealand;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class NewZealandAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'NZ';
    }

    public function format(AddressData $address): string
    {
        // UPU: 4 digits left of the locality.
        return AddressFormatRenderer::format('NZ', $address);
    }
}
