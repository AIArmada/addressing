<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\CaribbeanNetherlands;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class CaribbeanNetherlandsAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'BQ';
    }

    public function format(AddressData $address): string
    {
        // UPU: no postcode system yet (NL-style codes planned); prints any supplied code on its own line.
        return AddressFormatRenderer::format('BQ', $address);
    }
}
