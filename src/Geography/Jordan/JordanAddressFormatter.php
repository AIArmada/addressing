<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Jordan;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class JordanAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'JO';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5-digit postcode to the right of the locality name.
        return AddressFormatRenderer::format('JO', $address);
    }
}
