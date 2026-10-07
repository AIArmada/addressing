<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Cameroon;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class CameroonAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'CM';
    }

    public function format(AddressData $address): string
    {
        // UPU: no postcode system; delivery to street addresses and P.O. boxes.
        return AddressFormatRenderer::format('CM', $address);
    }
}
