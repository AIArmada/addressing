<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Ireland;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class IrelandAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'IE';
    }

    public function format(AddressData $address): string
    {
        // UPU: Eircode on its own line below the county.
        return AddressFormatRenderer::format('IE', $address);
    }
}
