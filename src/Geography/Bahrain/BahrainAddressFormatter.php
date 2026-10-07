<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Bahrain;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class BahrainAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'BH';
    }

    public function format(AddressData $address): string
    {
        // UPU: municipality + 3-4 digit postcode on one line.
        return AddressFormatRenderer::format('BH', $address);
    }
}
