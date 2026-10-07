<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Japan;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class JapanAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'JP';
    }

    public function format(AddressData $address): string
    {
        // UPU western layout: locality and prefecture share one line and
        // the NNN-NNNN postcode shares the last line with the country
        // (`231-0012 JAPAN` in all three detailed UPU examples).
        return AddressFormatRenderer::format('JP', $address);
    }
}
