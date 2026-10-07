<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\FrenchPolynesia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class FrenchPolynesiaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'PF';
    }

    public function format(AddressData $address): string
    {
        // UPU: 987xx left of the locality (French system).
        return AddressFormatRenderer::format('PF', $address);
    }
}
