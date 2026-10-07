<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Guernsey;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class GuernseyAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'GG';
    }

    public function format(AddressData $address): string
    {
        // UPU: UK-style postcode on its own line below the post town.
        return AddressFormatRenderer::format('GG', $address);
    }
}
