<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Nicaragua;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class NicaraguaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'NI';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits on their own line above the municipality.
        return AddressFormatRenderer::format('NI', $address);
    }
}
