<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Belarus;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class BelarusAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'BY';
    }

    public function format(AddressData $address): string
    {
        // UPU: 6 digits left of the locality with a comma.
        return AddressFormatRenderer::format('BY', $address);
    }
}
