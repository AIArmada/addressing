<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Vietnam;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class VietnamAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'VN';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5-digit postcode right of the province name.
        return AddressFormatRenderer::format('VN', $address);
    }
}
