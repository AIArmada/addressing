<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\CapeVerde;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class CapeVerdeAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'CV';
    }

    public function format(AddressData $address): string
    {
        // UPU: 4 digits, or 7-digit NNNN-NNN, left of the locality name.
        return AddressFormatRenderer::format('CV', $address);
    }
}
