<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Andorra;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class AndorraAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'AD';
    }

    public function format(AddressData $address): string
    {
        // UPU: AD + 3 digits left of the locality name.
        return AddressFormatRenderer::format('AD', $address);
    }
}
