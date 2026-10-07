<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Germany;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class GermanyAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'DE';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5-digit postcode to the left of the locality name; never prefix with D-.
        return AddressFormatRenderer::format('DE', $address);
    }
}
