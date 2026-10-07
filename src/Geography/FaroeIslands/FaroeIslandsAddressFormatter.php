<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\FaroeIslands;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class FaroeIslandsAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'FO';
    }

    public function format(AddressData $address): string
    {
        // UPU: FO-NNN left of the locality (prefix supplied as-is).
        return AddressFormatRenderer::format('FO', $address);
    }
}
