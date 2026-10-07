<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\UnitedArabEmirates;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class UnitedArabEmiratesAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'AE';
    }

    public function format(AddressData $address): string
    {
        // UPU: no postcode system; PO Box delivery only.
        return AddressFormatRenderer::format('AE', $address);
    }
}
