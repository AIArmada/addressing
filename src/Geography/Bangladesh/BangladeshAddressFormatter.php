<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Bangladesh;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class BangladeshAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'BD';
    }

    public function format(AddressData $address): string
    {
        // UPU: 4-digit postcode right of locality, dash-separated; thana optional.
        return AddressFormatRenderer::format('BD', $address);
    }
}
