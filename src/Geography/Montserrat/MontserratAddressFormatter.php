<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Montserrat;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class MontserratAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'MS';
    }

    public function format(AddressData $address): string
    {
        // UPU: MSR + 4 digits right of the locality with a comma.
        return AddressFormatRenderer::format('MS', $address);
    }
}
