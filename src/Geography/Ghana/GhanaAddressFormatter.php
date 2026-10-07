<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Ghana;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class GhanaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'GH';
    }

    public function format(AddressData $address): string
    {
        // UPU: postcode right of locality (N-123 or digital), region on its own line.
        return AddressFormatRenderer::format('GH', $address);
    }
}
