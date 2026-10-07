<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Tuvalu;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class TuvaluAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'TV';
    }

    public function format(AddressData $address): string
    {
        // UPU: no postcode system; prints any supplied code on its own line.
        return AddressFormatRenderer::format('TV', $address);
    }
}
