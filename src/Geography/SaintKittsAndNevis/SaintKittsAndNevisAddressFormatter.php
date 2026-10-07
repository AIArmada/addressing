<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\SaintKittsAndNevis;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class SaintKittsAndNevisAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'KN';
    }

    public function format(AddressData $address): string
    {
        // UPU: KN + 4 digits on its own line below the island.
        return AddressFormatRenderer::format('KN', $address);
    }
}
