<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\TimorLeste;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class TimorLesteAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'TL';
    }

    public function format(AddressData $address): string
    {
        // UPU: TL + 5 digits right of the locality name.
        return AddressFormatRenderer::format('TL', $address);
    }
}
