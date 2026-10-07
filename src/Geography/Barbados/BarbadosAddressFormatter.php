<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Barbados;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class BarbadosAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'BB';
    }

    public function format(AddressData $address): string
    {
        // UPU: BB + 5 digits right of the parish name.
        return AddressFormatRenderer::format('BB', $address);
    }
}
