<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Paraguay;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class ParaguayAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'PY';
    }

    public function format(AddressData $address): string
    {
        // UPU: 6 digits left of the locality (province follows on UPU lines).
        return AddressFormatRenderer::format('PY', $address);
    }
}
