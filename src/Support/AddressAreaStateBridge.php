<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Models\AddressArea;
use AIArmada\Addressing\Models\AddressAreaRelationship;
use AIArmada\Addressing\Models\AddressAreaStateLink;
use AIArmada\Addressing\Models\State;
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
    private const string REQUEST_CACHE_KEY = 'aiarmada.addressing.state-area-bridge';

    /**
     * Resolve the explicitly linked AddressArea node for a State.
     */
    public static function areaIdForState(State | string | null $state, ?string $hierarchyType = null): ?string
    {
        $cacheKey = self::stateCacheKey($state, $hierarchyType);

        if ($cacheKey !== null && app()->bound('request')) {
            $request = request();
            $cache = $request->attributes->get(self::REQUEST_CACHE_KEY, []);

            if (is_array($cache) && array_key_exists($cacheKey, $cache)) {
                $cachedAreaId = $cache[$cacheKey];

                return is_string($cachedAreaId) ? $cachedAreaId : null;
            }
        }

        $state = self::resolveState($state);

        if (! $state instanceof State) {
            self::remember($cacheKey, null);

            return null;
        }

        $linkTable = (new AddressAreaStateLink)->getTable();
        $areaTable = (new AddressArea)->getTable();
        $link = AddressAreaStateLink::query()
            ->join($areaTable, "{$areaTable}.id", '=', "{$linkTable}.address_area_id")
            ->where("{$linkTable}.state_id", $state->getKey())
            ->where("{$areaTable}.is_active", true)
            ->when($hierarchyType !== null, function ($query) use ($hierarchyType): void {
                $query->where(function ($query) use ($hierarchyType): void {
                    $query->where('hierarchy_type', $hierarchyType)
                        ->orWhereNull('hierarchy_type');
                })->orderByRaw('CASE WHEN hierarchy_type = ? THEN 0 ELSE 1 END', [$hierarchyType]);
            })
            ->orderByDesc("{$linkTable}.updated_at")
            ->orderByDesc("{$linkTable}.id")
            ->select("{$linkTable}.address_area_id")
            ->first();

        $areaId = $link?->getAttribute('address_area_id');
        self::remember($cacheKey, is_string($areaId) ? $areaId : null);

        return $areaId;
    }

    /**
     * Resolve a State from an explicitly linked AddressArea or any ancestor.
     */
    public static function stateIdForArea(AddressArea | string | null $area): ?string
    {
        $cacheKey = self::areaCacheKey($area);

        if ($cacheKey !== null && app()->bound('request')) {
            $cache = request()->attributes->get(self::REQUEST_CACHE_KEY, []);

            if (is_array($cache) && array_key_exists('area:' . $cacheKey, $cache)) {
                $cachedStateId = $cache['area:' . $cacheKey];

                return is_string($cachedStateId) ? $cachedStateId : null;
            }
        }

        $area = self::resolveArea($area);

        if (! $area instanceof AddressArea) {
            self::rememberArea($cacheKey, null);

            return null;
        }

        if ($area->getAttribute('is_active') === false) {
            self::rememberArea($cacheKey, null);

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

            $stateId = AddressAreaStateLink::query()
                ->whereIn('address_area_id', $frontier)
                ->whereHas('addressArea', fn ($query) => $query->where('is_active', true))
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->value('state_id');

            if (is_string($stateId) && $stateId !== '') {
                self::rememberArea($cacheKey, $stateId);

                return $stateId;
            }

            $parentIds = AddressAreaRelationship::query()
                ->whereIn('child_address_area_id', $frontier)
                ->where('relationship_type', 'contains')
                ->where(function ($query): void {
                    $query->whereNull('valid_from')->orWhereDate('valid_from', '<=', CarbonImmutable::now());
                })
                ->where(function ($query): void {
                    $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', CarbonImmutable::now());
                })
                ->whereHas('parent', fn ($query) => $query->where('is_active', true))
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->pluck('parent_address_area_id')
                ->all();

            $activeParentIds = AddressArea::query()
                ->whereIn('id', $parentIds)
                ->where('is_active', true)
                ->pluck('id')
                ->map(static fn (mixed $id): string => (string) $id)
                ->all();

            $pending = array_merge($pending, $activeParentIds);
        }

        self::rememberArea($cacheKey, null);

        return null;
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

    private static function remember(?string $key, ?string $areaId): void
    {
        if ($key === null || ! app()->bound('request')) {
            return;
        }

        $request = request();
        $cache = $request->attributes->get(self::REQUEST_CACHE_KEY, []);
        $cache = is_array($cache) ? $cache : [];
        $cache[$key] = $areaId;
        $request->attributes->set(self::REQUEST_CACHE_KEY, $cache);
    }

    private static function rememberArea(?string $key, ?string $stateId): void
    {
        if ($key === null || ! app()->bound('request')) {
            return;
        }

        $request = request();
        $cache = $request->attributes->get(self::REQUEST_CACHE_KEY, []);
        $cache = is_array($cache) ? $cache : [];
        $cache['area:' . $key] = $stateId;
        $request->attributes->set(self::REQUEST_CACHE_KEY, $cache);
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

        $found = AddressArea::query()->find($area);

        return $found instanceof AddressArea ? $found : null;
    }
}
