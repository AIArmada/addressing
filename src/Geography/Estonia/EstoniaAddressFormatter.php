<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Estonia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class EstoniaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'EE';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits left of the locality name.
        return AddressFormatRenderer::format('EE', $address);
    }
}
