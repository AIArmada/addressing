<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\FrenchSouthernTerritories;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressFormatRenderer;

final class FrenchSouthernTerritoriesAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'TF';
    }

    public function format(AddressData $address): string
    {
        // UPU: no postcode system (uninhabited); prints any supplied code on its own line.
        return AddressFormatRenderer::format('TF', $address);
    }
}
