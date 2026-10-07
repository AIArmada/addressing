<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Palau;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class PalauAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'PW';
    }

    public function format(AddressData $address): string
    {
        // UPU: US ZIP on the locality line: KOROR PW 96940.
        return AddressFormatRenderer::format('PW', $address);
    }
}
