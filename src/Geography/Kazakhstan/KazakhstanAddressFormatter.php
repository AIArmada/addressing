<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Kazakhstan;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class KazakhstanAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'KZ';
    }

    public function format(AddressData $address): string
    {
        // UPU: postcode (6-digit legacy or A99A9A9) left of the locality with a comma.
        return AddressFormatRenderer::format('KZ', $address);
    }
}
