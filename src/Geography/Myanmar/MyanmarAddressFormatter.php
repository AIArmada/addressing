<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Myanmar;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class MyanmarAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'MM';
    }

    public function format(AddressData $address): string
    {
        // UPU: locality and 7-digit postcode with a comma, region/state below.
        return AddressFormatRenderer::format('MM', $address);
    }
}
