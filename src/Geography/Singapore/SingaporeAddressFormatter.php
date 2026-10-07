<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Singapore;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class SingaporeAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'SG';
    }

    public function format(AddressData $address): string
    {
        return AddressFormatRenderer::format('SG', $address);
    }
}
