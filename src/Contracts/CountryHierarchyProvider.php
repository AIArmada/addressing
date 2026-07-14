<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

/**
 * Supplies a country's provider-specific AddressArea tree and optional State mappings.
 */
interface CountryHierarchyProvider
{
    public function addressAreaSource(): AddressAreaSource;

    /**
     * @return array<string, array{area_code: string, source: string, area_level: int}>
     */
    public function stateAreaMappings(): array;
}
