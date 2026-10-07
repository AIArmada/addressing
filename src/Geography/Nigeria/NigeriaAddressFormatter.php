<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Nigeria;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class NigeriaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'NG';
    }

    public function format(AddressData $address): string
    {
        // UPU: 6-digit postcode right of locality, state on its own line.
        return AddressFormatRenderer::format('NG', $address);
    }
}
