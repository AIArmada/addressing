<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\PapuaNewGuinea;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class PapuaNewGuineaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'PG';
    }

    public function format(AddressData $address): string
    {
        // UPU: 3 digits right of the locality: PORT MORESBY 111.
        return AddressFormatRenderer::format('PG', $address);
    }
}
