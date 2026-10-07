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
        // UPU: US ZIP on the locality line: POHNPEI FM 96941.
        return AddressFormatRenderer::format('FM', $address);
    }
}
