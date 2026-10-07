<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Madagascar;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class MadagascarAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'MG';
    }

    public function format(AddressData $address): string
    {
        // UPU: 3-digit postcode to the left of the postal town name.
        return AddressFormatRenderer::format('MG', $address);
    }
}
