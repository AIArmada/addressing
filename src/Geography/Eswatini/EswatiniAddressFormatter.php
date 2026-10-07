<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Eswatini;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class EswatiniAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'SZ';
    }

    public function format(AddressData $address): string
    {
        // UPU: letter + 3 digits on their own line below the locality.
        return AddressFormatRenderer::format('SZ', $address);
    }
}
