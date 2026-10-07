<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Afghanistan;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class AfghanistanAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'AF';
    }

    public function format(AddressData $address): string
    {
        // UPU: locality on its own line, then the 6-digit postcode left
        // of the province (`100208 KABUL` in every example; new system
        // from 1 October 2024, province-encoded).
        return AddressFormatRenderer::format('AF', $address);
    }
}
