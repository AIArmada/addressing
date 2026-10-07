<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Kuwait;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class KuwaitAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'KW';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5-digit postcode to the left of the locality name.
        return AddressFormatRenderer::format('KW', $address);
    }
}
