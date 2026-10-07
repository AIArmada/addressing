<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Russia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class RussiaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'RU';
    }

    public function format(AddressData $address): string
    {
        // UPU domestic layout puts the 6-digit postcode after the country; country is kept last here per the UPU IB recommendation.
        return AddressFormatRenderer::format('RU', $address);
    }
}
