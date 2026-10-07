<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Ethiopia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class EthiopiaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'ET';
    }

    public function format(AddressData $address): string
    {
        // UPU: 4-digit postcode to the left of the locality name.
        return AddressFormatRenderer::format('ET', $address);
    }
}
