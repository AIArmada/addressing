<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Malaysia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class MalaysiaAddressFormatter implements CountryAddressFormatter
{
    // Ruled 2026-10-08 (owner): no city level; postcode joins writer-computed
    // postcode_partner (town iff subdivision town-like, else daerah), ", " sep.
    public static function countryCode(): string
    {
        return 'MY';
    }

    public function format(AddressData $address): string
    {
        return AddressFormatRenderer::format('MY', $address);
    }
}
