<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Jersey;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class JerseyAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'JE';
    }

    public function format(AddressData $address): string
    {
        // UPU: UK-style postcode on its own line below the post town.
        return AddressFormatRenderer::format('JE', $address);
    }
}
