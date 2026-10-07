<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Tonga;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class TongaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'TO';
    }

    public function format(AddressData $address): string
    {
        // UPU: no postcode system; prints any supplied code on its own line.
        return AddressFormatRenderer::format('TO', $address);
    }
}
