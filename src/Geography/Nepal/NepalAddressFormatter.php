<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Nepal;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class NepalAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'NP';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits right of the locality name.
        return AddressFormatRenderer::format('NP', $address);
    }
}
