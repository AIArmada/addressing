<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Palau;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class PalauAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'PW';
    }

    public function format(AddressData $address): string
    {
        // USPS Pub 28 s224 + UPU PLW: island detail above; last line `CITY PW ZIP`.
        return AddressFormatRenderer::format('PW', $address);
    }
}
