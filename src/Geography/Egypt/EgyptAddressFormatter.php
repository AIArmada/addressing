<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Egypt;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class EgyptAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'EG';
    }

    public function format(AddressData $address): string
    {
        // UPU: locality, province, then 7-digit postcode on separate lines.
        return AddressFormatRenderer::format('EG', $address);
    }
}
