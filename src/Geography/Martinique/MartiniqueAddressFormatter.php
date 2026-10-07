<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Martinique;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class MartiniqueAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'MQ';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits (972xx) left of the locality (French system).
        return AddressFormatRenderer::format('MQ', $address);
    }
}
