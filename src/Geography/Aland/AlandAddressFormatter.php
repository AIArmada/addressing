<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Aland;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class AlandAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'AX';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits left of the town; intl mail prefixes AX- (supplied as-is).
        return AddressFormatRenderer::format('AX', $address);
    }
}
