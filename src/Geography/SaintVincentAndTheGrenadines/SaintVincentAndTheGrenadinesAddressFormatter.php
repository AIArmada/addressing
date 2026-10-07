<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\SaintVincentAndTheGrenadines;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class SaintVincentAndTheGrenadinesAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'VC';
    }

    public function format(AddressData $address): string
    {
        // UPU: VC + 4 digits on its own line below the locality.
        return AddressFormatRenderer::format('VC', $address);
    }
}
