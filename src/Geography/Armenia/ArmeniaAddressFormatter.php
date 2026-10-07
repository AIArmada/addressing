<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Armenia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class ArmeniaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'AM';
    }

    public function format(AddressData $address): string
    {
        // UPU: 4 digits left of the locality name.
        return AddressFormatRenderer::format('AM', $address);
    }
}
