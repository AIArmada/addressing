<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Lebanon;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class LebanonAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'LB';
    }

    public function format(AddressData $address): string
    {
        // UPU: optional 4+4 digit postcode right of the locality name.
        return AddressFormatRenderer::format('LB', $address);
    }
}
