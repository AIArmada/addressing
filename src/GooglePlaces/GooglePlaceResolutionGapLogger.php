<?php

declare(strict_types=1);

namespace AIArmada\Addressing\GooglePlaces;

use AIArmada\Addressing\Actions\LogAddressResolutionGapAction;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressArea;
use AIArmada\Addressing\Models\AddressAreaName;

final readonly class GooglePlaceResolutionGapLogger
{
    public function __construct(private GooglePlaceGeographyMatcher $matcher) {}

    /**
     * @param  array<string, list<string>>  $misses
     * @param  array<string, array{hierarchy: AddressHierarchyDefinition, level: AddressLevelDefinition, area: AddressArea}>  $resolved
     */
    public function log(
        array $misses,
        array $resolved,
        ?string $stateName,
        ?string $stateId,
        bool $expectsState,
        ?string $countryCode,
        ?string $placeId,
    ): void {
        if ($countryCode === null || mb_strlen($countryCode) !== 2) {
            return;
        }

        $resolvedNames = $this->resolvedAreaNames($resolved, $countryCode);
        $logger = app(LogAddressResolutionGapAction::class);
        $context = $placeId !== null ? ['place_id' => $placeId] : [];

        foreach ($misses as $role => $names) {
            if (isset($resolved[$role]) || $names === []) {
                continue;
            }

            $value = $names[0];
            $normalized = $this->matcher->normalizeLocationName($value, $countryCode);

            if ($normalized === '' || isset($resolvedNames[$normalized]) || mb_strlen($value) > 255) {
                continue;
            }

            $logger->execute(
                source: 'google-picker',
                countryCode: $countryCode,
                role: $role,
                value: $value,
                context: [...$context, 'attempted' => $names],
            );
        }

        if ($expectsState && $stateId === null && filled($stateName) && mb_strlen($stateName) <= 255) {
            $logger->execute(
                source: 'google-picker',
                countryCode: $countryCode,
                role: 'state',
                value: $stateName,
                context: [...$context, 'attempted' => [$stateName]],
            );
        }
    }

    /**
     * @param  array<string, array{hierarchy: AddressHierarchyDefinition, level: AddressLevelDefinition, area: AddressArea}>  $resolved
     * @return array<string, true>
     */
    private function resolvedAreaNames(array $resolved, ?string $countryCode): array
    {
        $names = [];
        $areaIds = [];

        foreach ($resolved as $entry) {
            $area = $entry['area'];
            $areaIds[] = (string) $area->getKey();
            $normalized = $this->matcher->normalizeLocationName((string) $area->name, $countryCode);

            if ($normalized !== '') {
                $names[$normalized] = true;
            }
        }

        if ($areaIds === []) {
            return $names;
        }

        $aliases = AddressAreaName::query()
            ->whereIn('address_area_id', array_values(array_unique($areaIds)))
            ->pluck('name');

        foreach ($aliases as $alias) {
            if (! is_string($alias)) {
                continue;
            }

            $normalized = $this->matcher->normalizeLocationName($alias, $countryCode);

            if ($normalized !== '') {
                $names[$normalized] = true;
            }
        }

        return $names;
    }
}
