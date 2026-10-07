<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Eritrea;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class EritreaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'ER';
    }

    public function format(AddressData $address): string
    {
        // UPU: no postcode system; prints any supplied code on its own line.
        return AddressFormatRenderer::format('ER', $address);
    }
}
