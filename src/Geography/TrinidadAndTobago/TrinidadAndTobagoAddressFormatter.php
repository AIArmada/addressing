<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\TrinidadAndTobago;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class TrinidadAndTobagoAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'TT';
    }

    public function format(AddressData $address): string
    {
        // UPU: 6 digits right of the locality name.
        return AddressFormatRenderer::format('TT', $address);
    }
}
