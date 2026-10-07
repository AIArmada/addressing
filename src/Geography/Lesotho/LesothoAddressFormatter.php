<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Lesotho;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class LesothoAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'LS';
    }

    public function format(AddressData $address): string
    {
        // UPU: 3 digits right of the locality name.
        return AddressFormatRenderer::format('LS', $address);
    }
}
