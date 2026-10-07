<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Monaco;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class MonacoAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'MC';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits (98xxx) left of the locality name.
        return AddressFormatRenderer::format('MC', $address);
    }
}
