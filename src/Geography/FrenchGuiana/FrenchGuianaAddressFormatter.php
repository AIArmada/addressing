<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\FrenchGuiana;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class FrenchGuianaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'GF';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits (973xx) left of the locality (French system).
        return AddressFormatRenderer::format('GF', $address);
    }
}
