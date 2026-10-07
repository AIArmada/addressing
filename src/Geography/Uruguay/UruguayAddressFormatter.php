<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Uruguay;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class UruguayAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'UY';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits left of the locality with an en dash.
        return AddressFormatRenderer::format('UY', $address);
    }
}
