<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Guam;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class GuamAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'GU';
    }

    public function format(AddressData $address): string
    {
        // UPU: US ZIP on the locality line: BARRIGADA GU 96913.
        return AddressFormatRenderer::format('GU', $address);
    }
}
