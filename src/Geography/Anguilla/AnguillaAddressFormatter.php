<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Anguilla;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class AnguillaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'AI';
    }

    public function format(AddressData $address): string
    {
        // UPU: single AI-2640 code on its own line below the locality.
        return AddressFormatRenderer::format('AI', $address);
    }
}
