<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Bermuda;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class BermudaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'BM';
    }

    public function format(AddressData $address): string
    {
        // UPU: dual AA NN / AA AA code right of the parish name.
        return AddressFormatRenderer::format('BM', $address);
    }
}
