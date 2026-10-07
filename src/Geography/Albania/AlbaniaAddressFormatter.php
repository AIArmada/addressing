<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Albania;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class AlbaniaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'AL';
    }

    public function format(AddressData $address): string
    {
        // UPU: 4 digits on their own line above the locality.
        return AddressFormatRenderer::format('AL', $address);
    }
}
