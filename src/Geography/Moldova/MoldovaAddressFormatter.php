<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Moldova;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class MoldovaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'MD';
    }

    public function format(AddressData $address): string
    {
        // UPU: MD-NNNN left of the locality with a comma (prefix supplied as-is).
        return AddressFormatRenderer::format('MD', $address);
    }
}
