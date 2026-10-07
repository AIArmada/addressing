<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\AntiguaAndBarbuda;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class AntiguaAndBarbudaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'AG';
    }

    public function format(AddressData $address): string
    {
        // UPU: no postcode system; prints any supplied code on its own line.
        return AddressFormatRenderer::format('AG', $address);
    }
}
