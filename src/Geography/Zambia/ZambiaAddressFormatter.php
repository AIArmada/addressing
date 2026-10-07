<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Zambia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class ZambiaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'ZM';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits right of the locality name (routinely omitted in practice).
        return AddressFormatRenderer::format('ZM', $address);
    }
}
