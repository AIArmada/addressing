<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Japan;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class JapanAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'JP';
    }

    public function format(AddressData $address): string
    {
        // Western letter layout: `{city}, {prefecture} {postcode}` on one
        // line, country alone last. USPS Pub 28 A3 bars postcodes from the
        // last line, and Japan Post's western order joins the code to the
        // prefecture; the joined `NNN-NNNN JAPAN` shape is the domestic
        // 〒-envelope convention, not letter order.
        return AddressFormatRenderer::format('JP', $address);
    }
}
