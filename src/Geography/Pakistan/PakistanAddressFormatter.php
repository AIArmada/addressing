<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Pakistan;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class PakistanAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'PK';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5-digit postcode right of locality, dash-separated.
        return AddressFormatRenderer::format('PK', $address);
    }
}
