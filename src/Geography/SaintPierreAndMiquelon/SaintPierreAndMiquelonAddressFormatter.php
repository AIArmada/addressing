<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\SaintPierreAndMiquelon;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class SaintPierreAndMiquelonAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'PM';
    }

    public function format(AddressData $address): string
    {
        // UPU: sole code 97500 left of the locality (French system).
        return AddressFormatRenderer::format('PM', $address);
    }
}
