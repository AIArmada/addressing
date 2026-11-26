<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\Geography\Support;

use AIArmada\Addressing\Features\Geography\Models\AddressArea;
use AIArmada\Addressing\Features\Geography\Models\State;
use AIArmada\Addressing\Support\ModelResolver;
use AIArmada\Addressing\Support\RequestCache;
use Carbon\CarbonImmutable;

/**
 * Resolves an optional explicit relationship between canonical State rows and
 * provider-specific AddressArea hierarchies.
 *
 * State and AddressArea are independent package concepts. A host application
 * only needs this bridge when it wants to use an AddressArea tree as a child
 * selector for its State rows.
 */
final class AddressAreaStateBridge
{
    private const string CACHE_BUCKET = 'hierarchy-ancestors';

    /**
     * Resolve the explicitly linked AddressArea node for a State.
     */
    public static function areaIdForState(State | string | null $state, ?string $hierarchyType = null): ?string
    {
        $cacheKey = self::stateCacheKey($state, $hierarchyType);

        if ($cacheKey === null) {
            return self::resolveAreaIdForState($state, $hierarchyType);
        }

        $linkClass = ModelResolver::areaStateLinkClass();
        $scopeKey = implode('|', [
            $cacheKey,
            ModelResolver::stateClass(),
            ModelResolver::areaClass(),
        ]);

        $areaId = RequestCache::remember(
            self::CACHE_BUCKET,
            new $linkClass,
            'state:' . $scopeKey,
            static fn (): ?string => self::resolveAreaIdForState($state, $hierarchyType),
        );

        return is_string($areaId) ? $areaId : null;
    }

    private static function resolveAreaIdForState(State | string | null $state, ?string $hierarchyType): ?string
    {
        $state = self::resolveState($state);

        if (! $state instanceof State) {
            return null;
        }

        $areaId = ModelResolver::areaStateLinkClass()::query()
            ->where('state_id', $state->getKey())
            ->whereHas('addressArea', fn ($query) => $query->where('is_active', true)->where('country_id', $state->country_id))
            ->when($hierarchyType !== null, function ($query) use ($hierarchyType): void {
                $query->where(function ($query) use ($hierarchyType): void {
                    $query->where('hierarchy_type', $hierarchyType)
                        ->orWhere('hierarchy_type', '');
                })->orderByRaw('CASE WHEN hierarchy_type = ? THEN 0 ELSE 1 END', [$hierarchyType]);
            })
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->value('address_area_id');

        return is_string($areaId) ? $areaId : null;
    }

    /**
     * Forget hierarchy bridge entries after links for a state are removed.
     *
     * Forward and reverse lookups share one memo bucket, so invalidating
     * the hierarchy bucket keeps both directions from returning mappings
     * for the deleted state later in the same request.
     */
    public static function forgetState(State | string | null $state): void
    {
        $stateId = $state instanceof State ? (string) $state->getKey() : $state;

        if (! is_string($stateId) || $stateId === '') {
            return;
        }

        self::forgetAll();
    }

    /**
     * Resolve a State from an explicitly linked AddressArea or any ancestor.
     */
    public static function stateIdForArea(AddressArea | string | null $area): ?string
    {
        $cacheKey = self::areaCacheKey($area);

        if ($cacheKey === null) {
            return self::resolveStateIdForArea($area);
        }

        $areaClass = ModelResolver::areaClass();
        $scopeKey = implode('|', [
            $cacheKey,
            ModelResolver::stateClass(),
            ModelResolver::areaStateLinkClass(),
            ModelResolver::areaRelationshipClass(),
        ]);

        $stateId = RequestCache::remember(
            self::CACHE_BUCKET,
            new $areaClass,
            'area:' . $scopeKey,
            static fn (): ?string => self::resolveStateIdForArea($area),
        );

        return is_string($stateId) ? $stateId : null;
    }

    private static function resolveStateIdForArea(AddressArea | string | null $area): ?string
    {
        $area = self::resolveArea($area);

        if (! $area instanceof AddressArea) {
            return null;
        }

        if ($area->getAttribute('is_active') === false) {
            return null;
        }

        $pending = [(string) $area->getKey()];
        $visited = [];

        while ($pending !== []) {
            $frontier = array_values(array_filter(
                array_unique($pending),
                static fn (mixed $areaId): bool => is_string($areaId) && ! isset($visited[$areaId]),
            ));
            $pending = [];

            if ($frontier === []) {
                break;
            }

            foreach ($frontier as $areaId) {
                $visited[$areaId] = true;
            }

            $stateId = ModelResolver::areaStateLinkClass()::query()
                ->whereIn('address_area_id', $frontier)
                ->whereHas('state', fn ($query) => $query->where('country_id', $area->country_id))
                ->whereHas('addressArea', fn ($query) => $query->where('is_active', true)->where('country_id', $area->country_id))
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->value('state_id');

            if (is_string($stateId) && $stateId !== '') {
                return $stateId;
            }

            $parentIds = ModelResolver::areaRelationshipClass()::query()
                ->whereIn('child_address_area_id', $frontier)
                ->where('relationship_type', 'contains')
                ->where(function ($query): void {
                    $query->whereNull('valid_from')->orWhereDate('valid_from', '<=', CarbonImmutable::now());
                })
                ->where(function ($query): void {
                    $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', CarbonImmutable::now());
                })
                ->whereHas('parent', fn ($query) => $query->where('is_active', true)->where('country_id', $area->country_id))
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->pluck('parent_address_area_id')
                ->all();

            $pending = array_map(static fn (mixed $id): string => (string) $id, $parentIds);
        }

        return null;
    }

    public static function forgetAll(): void
    {
        RequestCache::forget(self::CACHE_BUCKET);
    }

    private static function resolveState(State | string | null $state): ?State
    {
        if ($state instanceof State) {
            return $state;
        }

        if (! is_string($state) || $state === '') {
            return null;
        }

        $stateClass = ModelResolver::stateClass();
        $found = $stateClass::query()->find($state);

        return $found instanceof State ? $found : null;
    }

    private static function stateCacheKey(State | string | null $state, ?string $hierarchyType): ?string
    {
        $stateId = $state instanceof State ? $state->getKey() : $state;

        if (! is_string($stateId) || $stateId === '') {
            return null;
        }

        return $stateId . ':' . ($hierarchyType ?? 'any');
    }

    private static function areaCacheKey(AddressArea | string | null $area): ?string
    {
        $areaId = $area instanceof AddressArea ? $area->getKey() : $area;

        return is_string($areaId) && $areaId !== '' ? $areaId : null;
    }

    private static function resolveArea(AddressArea | string | null $area): ?AddressArea
    {
        if ($area instanceof AddressArea) {
            return $area;
        }

        if (! is_string($area) || $area === '') {
            return null;
        }

        $found = ModelResolver::areaClass()::query()->find($area);

        return $found instanceof AddressArea ? $found : null;
    }
}
