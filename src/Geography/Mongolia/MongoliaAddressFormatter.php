<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Mongolia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class MongoliaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'MN';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits (optionally -NNNN) right of the province or capital name.
        return AddressFormatRenderer::format('MN', $address);
    }
}
