<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\SriLanka;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class SriLankaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'LK';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits on their own line below the locality.
        return AddressFormatRenderer::format('LK', $address);
    }
}
