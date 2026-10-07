<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Azerbaijan;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class AzerbaijanAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'AZ';
    }

    public function format(AddressData $address): string
    {
        // UPU: AZ + 4 digits left of the locality name.
        return AddressFormatRenderer::format('AZ', $address);
    }
}
