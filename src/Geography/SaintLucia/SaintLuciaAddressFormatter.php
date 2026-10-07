<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\SaintLucia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class SaintLuciaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'LC';
    }

    public function format(AddressData $address): string
    {
        // UPU: LC + district + area right of the locality with a comma.
        return AddressFormatRenderer::format('LC', $address);
    }
}
