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
        // UPU GTM: postcode + municipality, department comma-joined (dash only sometimes).
        return AddressFormatRenderer::format('GT', $address);
    }
}
