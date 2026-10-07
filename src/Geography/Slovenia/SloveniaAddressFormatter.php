<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Slovenia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class SloveniaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'SI';
    }

    public function format(AddressData $address): string
    {
        // UPU: 4 digits left of the locality (SI- prefix passes through as supplied).
        return AddressFormatRenderer::format('SI', $address);
    }
}
