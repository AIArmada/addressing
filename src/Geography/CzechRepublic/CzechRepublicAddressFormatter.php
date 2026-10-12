<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\CzechRepublic;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class CzechRepublicAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'CZ';
    }

    public function format(AddressData $address): string
    {
        // UPU CZE: NNN NN left of the locality with TWO spaces (AU/NL precedent).
        return AddressFormatRenderer::format('CZ', $address);
    }
}
