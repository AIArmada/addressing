<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Qatar;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class QatarAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'QA';
    }

    public function format(AddressData $address): string
    {
        // UPU: no postcode system; PO Box or zone/street delivery.
        return AddressFormatRenderer::format('QA', $address);
    }
}
