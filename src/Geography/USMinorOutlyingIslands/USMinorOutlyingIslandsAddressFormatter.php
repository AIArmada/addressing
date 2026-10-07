<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\USMinorOutlyingIslands;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class USMinorOutlyingIslandsAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'UM';
    }

    public function format(AddressData $address): string
    {
        // UPU umiEn: no UM domestic system (Wake station mail routes via US ZIP 96898); prints any supplied code on its own line below.
        return AddressFormatRenderer::format('UM', $address);
    }
}
