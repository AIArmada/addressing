<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Laos;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class LaosAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'LA';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits left of the locality name.
        return AddressFormatRenderer::format('LA', $address);
    }
}
