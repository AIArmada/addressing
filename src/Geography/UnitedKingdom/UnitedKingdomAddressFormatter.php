<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\UnitedKingdom;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class UnitedKingdomAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'GB';
    }

    public function format(AddressData $address): string
    {
        // UPU: post town then postcode below; county omitted with postcode.
        return AddressFormatRenderer::format('GB', $address);
    }
}
