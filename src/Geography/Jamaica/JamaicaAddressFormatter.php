<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Jamaica;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class JamaicaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'JM';
    }

    public function format(AddressData $address): string
    {
        // UPU: no national system; Kingston sector suffix stays in the locality line.
        return AddressFormatRenderer::format('JM', $address);
    }
}
