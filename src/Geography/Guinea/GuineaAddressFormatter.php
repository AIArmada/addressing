<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Guinea;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class GuineaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'GN';
    }

    public function format(AddressData $address): string
    {
        // UPU: 3-digit radical left of the locality (BP suffix stays in street lines).
        return AddressFormatRenderer::format('GN', $address);
    }
}
