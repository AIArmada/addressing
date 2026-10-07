<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Djibouti;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class DjiboutiAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'DJ';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits left of the locality name.
        return AddressFormatRenderer::format('DJ', $address);
    }
}
