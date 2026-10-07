<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Ukraine;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class UkraineAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'UA';
    }

    public function format(AddressData $address): string
    {
        // UPU: locality, oblast, then 5-digit postcode on separate lines.
        return AddressFormatRenderer::format('UA', $address);
    }
}
