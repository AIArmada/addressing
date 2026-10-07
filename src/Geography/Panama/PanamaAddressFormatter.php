<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Panama;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class PanamaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'PA';
    }

    public function format(AddressData $address): string
    {
        // UPU: no postcode system (box zone numbers are not postcodes).
        return AddressFormatRenderer::format('PA', $address);
    }
}
