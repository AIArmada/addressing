<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\Geography\Api;

interface AreaReferenceReader
{
    /**
     * @param  list<string>  $areaIds
     * @return list<array{area_id: string, name: string, name_type: string, is_preferred: bool}>
     */
    public function aliases(array $areaIds, ?string $source = null): array;

    /**
     * @param  list<string>  $childIds
     * @return list<array{parent_id: string, child_id: string, hierarchy_type: ?string}>
     */
    public function activeContainmentParents(array $childIds): array;

    public function countryExists(string $countryCode): bool;

    /** @return list<string> */
    public function countryCodes(): array;

    public function areaIdForSource(string $countryCode, string $source, string $sourceId): ?string;

    /** @return array<string, string> */
    public function areaIdsForSource(string $countryCode, string $source): array;

    /**
     * @param  list<string>  $areaIds
     * @return array<string, array{source_id: string, name: string}>
     */
    public function areaMetadata(array $areaIds): array;
}
