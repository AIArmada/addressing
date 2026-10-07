<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Peru;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class PeruAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'PE';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5-digit postcode above the province line.
        return AddressFormatRenderer::format('PE', $address);
    }
}
