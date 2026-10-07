<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\SaintMartin;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class SaintMartinAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'MF';
    }

    public function format(AddressData $address): string
    {
        // UPU: single 97150 code left of the locality (French system).
        return AddressFormatRenderer::format('MF', $address);
    }
}
