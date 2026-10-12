<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Micronesia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class MicronesiaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'FM';
    }

    public function format(AddressData $address): string
    {
        // USPS Pub 28 s224 + UPU FSM: island/state detail above; last line `CITY FM ZIP`.
        return AddressFormatRenderer::format('FM', $address);
    }
}
