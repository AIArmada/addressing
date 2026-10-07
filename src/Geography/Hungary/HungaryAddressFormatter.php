<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Hungary;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class HungaryAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'HU';
    }

    public function format(AddressData $address): string
    {
        // UPU: 4 digits left of the town (intl one-line practice; domestic prints it below).
        return AddressFormatRenderer::format('HU', $address);
    }
}
