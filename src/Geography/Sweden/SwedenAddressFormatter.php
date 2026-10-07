<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Sweden;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class SwedenAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'SE';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits as XXX XX left of the locality (SE- prefix passes through).
        return AddressFormatRenderer::format('SE', $address);
    }
}
