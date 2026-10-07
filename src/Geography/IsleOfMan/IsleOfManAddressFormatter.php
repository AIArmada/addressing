<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\IsleOfMan;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class IsleOfManAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'IM';
    }

    public function format(AddressData $address): string
    {
        // UPU: UK-style postcode on its own line below the post town.
        return AddressFormatRenderer::format('IM', $address);
    }
}
