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
     * Keys are state codes. Numeric ISO codes (e.g. BH-13) surface as int keys
     * because PHP casts numeric-string array keys; consumers must stringify.
     *
     * @return array<int|string, array{area_code: string, source: string, area_level: int, hierarchy_types?: list<string>}>
     */
    public function stateAreaMappings(): array;
}
