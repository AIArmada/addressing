<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Spain;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class SpainAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'ES';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5-digit postcode left of locality, province on its own line.
        return AddressFormatRenderer::format('ES', $address);
    }
}
