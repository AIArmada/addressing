<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Philippines;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class PhilippinesAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'PH';
    }

    public function format(AddressData $address): string
    {
        // UPU: 4-digit postcode left of the province name, with the
        // municipality on its own line above. Metro Manila instead joins
        // municipality and region on the postcode line.
        return AddressFormatRenderer::format('PH', $address);
    }
}
