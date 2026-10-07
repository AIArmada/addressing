<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\PuertoRico;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class PuertoRicoAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'PR';
    }

    public function format(AddressData $address): string
    {
        // UPU: US ZIP on the locality line: LOCALITY PR ZIP.
        return AddressFormatRenderer::format('PR', $address);
    }
}
