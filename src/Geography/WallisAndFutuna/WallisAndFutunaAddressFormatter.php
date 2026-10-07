<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\WallisAndFutuna;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class WallisAndFutunaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'WF';
    }

    public function format(AddressData $address): string
    {
        // UPU: 986xx left of the locality (French system).
        return AddressFormatRenderer::format('WF', $address);
    }
}
