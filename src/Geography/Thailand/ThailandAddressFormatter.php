<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Thailand;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class ThailandAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'TH';
    }

    public function format(AddressData $address): string
    {
        // UPU: district and province, then 5-digit postcode below.
        return AddressFormatRenderer::format('TH', $address);
    }
}
