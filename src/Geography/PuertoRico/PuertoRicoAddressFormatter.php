<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\PuertoRico;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class PuertoRicoAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'PR';
    }

    public function format(AddressData $address): string
    {
        // USPS Pub 28 s224 + UPU PRI: barrio detail above; last line `CITY PR ZIP`.
        return AddressFormatRenderer::format('PR', $address);
    }
}
