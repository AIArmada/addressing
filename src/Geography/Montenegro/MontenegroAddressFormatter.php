<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Montenegro;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class MontenegroAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'ME';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits left of the locality name.
        return AddressFormatRenderer::format('ME', $address);
    }
}
