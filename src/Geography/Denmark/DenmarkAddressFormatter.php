<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Denmark;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class DenmarkAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'DK';
    }

    public function format(AddressData $address): string
    {
        // UPU: 4 digits left of the locality (optional DK- passes through).
        return AddressFormatRenderer::format('DK', $address);
    }
}
