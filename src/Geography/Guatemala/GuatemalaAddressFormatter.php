<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Guatemala;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class GuatemalaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'GT';
    }

    public function format(AddressData $address): string
    {
        // UPU: 5 digits left of the locality with a hyphen (bare form also seen).
        return AddressFormatRenderer::format('GT', $address);
    }
}
