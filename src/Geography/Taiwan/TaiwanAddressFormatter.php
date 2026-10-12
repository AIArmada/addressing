<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Taiwan;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class TaiwanAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'TW';
    }

    public function format(AddressData $address): string
    {
        // Chunghwa Post: `district, city postcode` (`Da-an District, Taipei City 106409`).
        return AddressFormatRenderer::format('TW', $address);
    }
}
