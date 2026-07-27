<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

use AIArmada\Addressing\Data\AddressHierarchyDefinition;

/**
 * Describes a country's address structure without imposing it on other countries.
 */
interface CountryAddressProfile
{
    public function countryCode(): string;

    /** @return list<AddressHierarchyDefinition> */
    public function addressHierarchies(): array;
}
