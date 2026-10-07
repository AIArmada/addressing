<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Chile;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class ChileAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'CL';
    }

    public function format(AddressData $address): string
    {
        // UPU: 7 digits left of the commune name.
        return AddressFormatRenderer::format('CL', $address);
    }
}
