<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Models\AddressArea;
use AIArmada\Addressing\Models\AddressAreaRelationship;
use AIArmada\Addressing\Models\AddressAreaStateLink;
use AIArmada\Addressing\Models\State;

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
    /**
     * Resolve the explicitly linked AddressArea node for a State.
     */
    public static function areaIdForState(State | string | null $state, ?string $hierarchyType = null): ?string
    {
        $state = self::resolveState($state);

        if (! $state instanceof State) {
            return null;
        }

        $link = AddressAreaStateLink::query()
            ->where('state_id', $state->getKey())
            ->whereHas('addressArea', fn ($query) => $query->where('is_active', true))
            ->when($hierarchyType !== null, function ($query) use ($hierarchyType): void {
                $query->where(function ($query) use ($hierarchyType): void {
                    $query->where('hierarchy_type', $hierarchyType)
                        ->orWhereNull('hierarchy_type');
                })->orderByRaw('CASE WHEN hierarchy_type = ? THEN 0 ELSE 1 END', [$hierarchyType]);
            })
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->with('addressArea')
            ->first();

        return $link?->addressArea?->getKey();
    }

    /**
     * Resolve a State from an explicitly linked AddressArea or any ancestor.
     */
    public static function stateIdForArea(AddressArea | string | null $area): ?string
    {
        $area = self::resolveArea($area);

        if (! $area instanceof AddressArea) {
            return null;
        }

        if (! AddressArea::query()->whereKey($area->getKey())->where('is_active', true)->exists()) {
            return null;
        }

        $pending = [(string) $area->getKey()];
        $visited = [];

        while ($pending !== []) {
            $areaId = array_shift($pending);

            if (! is_string($areaId) || isset($visited[$areaId])) {
                continue;
            }

            $visited[$areaId] = true;

            $stateId = AddressAreaStateLink::query()
                ->where('address_area_id', $areaId)
                ->whereHas('addressArea', fn ($query) => $query->where('is_active', true))
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->value('state_id');

            if (is_string($stateId) && $stateId !== '') {
                return $stateId;
            }

            $parentIds = AddressAreaRelationship::query()
                ->where('child_address_area_id', $areaId)
                ->where('relationship_type', 'contains')
                ->where(function ($query): void {
                    $query->whereNull('valid_from')->orWhereDate('valid_from', '<=', now());
                })
                ->where(function ($query): void {
                    $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', now());
                })
                ->whereHas('parent', fn ($query) => $query->where('is_active', true))
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->pluck('parent_address_area_id');

            foreach ($parentIds as $parentId) {
                $pending[] = (string) $parentId;
            }
        }

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
