<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

use AIArmada\Addressing\Data\AddressLevelDefinition;

/**
 * Describes a country's address structure without imposing it on other countries.
 */
interface CountryAddressProfile
{
    public function countryCode(): string;

    /**
     * @return list<AddressLevelDefinition>
     */
    public function addressLevels(): array;
}
