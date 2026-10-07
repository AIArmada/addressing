<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Gabon;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class GabonAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'GA';
    }

    public function format(AddressData $address): string
    {
        // UPU: 2-digit zone left of the locality (UPU line adds the office code right: NN LOCALITY NN).
        return AddressFormatRenderer::format('GA', $address);
    }
}
