<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Honduras;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class HondurasAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'HN';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits left of the city (2004 alphanumeric UPU sheet superseded).
        return AddressFormatRenderer::format('HN', $address);
    }
}
