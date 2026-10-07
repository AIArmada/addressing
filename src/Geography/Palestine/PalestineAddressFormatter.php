<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Palestine;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class PalestineAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'PS';
    }

    public function format(AddressData $address): string
    {
        // UPU: P + 7 digits (short P + 3 passes through) right of the locality.
        return AddressFormatRenderer::format('PS', $address);
    }
}
