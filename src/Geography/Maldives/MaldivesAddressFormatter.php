<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Maldives;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class MaldivesAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'MV';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits right of the locality name.
        return AddressFormatRenderer::format('MV', $address);
    }
}
