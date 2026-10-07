<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Senegal;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class SenegalAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'SN';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits, often written CP NNNNN, left of the delivery office.
        return AddressFormatRenderer::format('SN', $address);
    }
}
