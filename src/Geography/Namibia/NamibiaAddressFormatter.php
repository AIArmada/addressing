<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Namibia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class NamibiaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'NA';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits on their own line below the locality.
        return AddressFormatRenderer::format('NA', $address);
    }
}
