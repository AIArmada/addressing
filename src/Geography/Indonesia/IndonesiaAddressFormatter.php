<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Indonesia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class IndonesiaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'ID';
    }

    public function format(AddressData $address): string
    {
        return AddressFormatRenderer::format('ID', $address);
    }
}
