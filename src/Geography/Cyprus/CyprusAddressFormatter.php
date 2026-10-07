<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Cyprus;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class CyprusAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'CY';
    }

    public function format(AddressData $address): string
    {
        // UPU: 4 digits left of the locality; inbound mail prefixes CY- (supplied as-is).
        return AddressFormatRenderer::format('CY', $address);
    }
}
