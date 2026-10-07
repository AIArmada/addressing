<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Guadeloupe;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class GuadeloupeAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'GP';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits (971xx) left of the locality (French system).
        return AddressFormatRenderer::format('GP', $address);
    }
}
