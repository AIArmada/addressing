<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Guyana;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class GuyanaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'GY';
    }

    public function format(AddressData $address): string
    {
        // UPU: 7 digits on their own line below the locality (2025 system).
        return AddressFormatRenderer::format('GY', $address);
    }
}
