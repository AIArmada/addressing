<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Malta;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class MaltaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'MT';
    }

    public function format(AddressData $address): string
    {
        // UPU: AAA NNNN on its own line below the locality.
        return AddressFormatRenderer::format('MT', $address);
    }
}
