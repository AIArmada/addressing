<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Croatia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class CroatiaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'HR';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits left of the locality; inbound mail prefixes HR- (supplied as-is).
        return AddressFormatRenderer::format('HR', $address);
    }
}
