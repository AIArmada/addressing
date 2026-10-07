<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Iran;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class IranAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'IR';
    }

    public function format(AddressData $address): string
    {
        // UPU: 10 digits on their own line below the province.
        return AddressFormatRenderer::format('IR', $address);
    }
}
