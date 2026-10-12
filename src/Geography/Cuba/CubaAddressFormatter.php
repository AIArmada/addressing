<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Cuba;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class CubaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'CU';
    }

    public function format(AddressData $address): string
    {
        // UPU CUB: `CP 10600 CIUDAD HABANA`; CP is domestic usage, baked into the format.
        return AddressFormatRenderer::format('CU', $address);
    }
}
