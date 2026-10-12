<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Iran;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class IranAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'IR';
    }

    public function format(AddressData $address): string
    {
        // UPU IRN: locality first, then street, province, postcode on its own line.
        return AddressFormatRenderer::format('IR', $address);
    }
}
