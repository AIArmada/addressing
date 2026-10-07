<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Turkiye;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class TurkiyeAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'TR';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5-digit postcode left of locality/province (06050-01 sub-locality numbers unsupported).
        return AddressFormatRenderer::format('TR', $address);
    }
}
