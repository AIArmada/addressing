<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\TurksAndCaicos;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class TurksAndCaicosAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'TC';
    }

    public function format(AddressData $address): string
    {
        // UPU: single TKCA 1ZZ code on its own line below the locality.
        return AddressFormatRenderer::format('TC', $address);
    }
}
