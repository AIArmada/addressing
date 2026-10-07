<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\France;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class FranceAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'FR';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5-digit postcode to the left of the locality name (CEDEX lines unsupported).
        return AddressFormatRenderer::format('FR', $address);
    }
}
