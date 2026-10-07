<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\MarshallIslands;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class MarshallIslandsAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'MH';
    }

    public function format(AddressData $address): string
    {
        // UPU: US ZIP on the locality line: MAJURO MH 96960.
        return AddressFormatRenderer::format('MH', $address);
    }
}
