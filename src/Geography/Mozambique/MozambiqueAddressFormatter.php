<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Mozambique;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class MozambiqueAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'MZ';
    }

    public function format(AddressData $address): string
    {
        // UPU: 4-digit postcode left of locality, province on its own line.
        return AddressFormatRenderer::format('MZ', $address);
    }
}
