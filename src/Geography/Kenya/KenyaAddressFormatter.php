<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Kenya;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class KenyaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'KE';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5-digit postcode below the post office, then town; county omitted.
        return AddressFormatRenderer::format('KE', $address);
    }
}
