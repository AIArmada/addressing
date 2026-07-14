<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Models\AddressArea;
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
    public static function areaIdForState(State | string | null $state): ?string
    {
        $state = self::resolveState($state);

        if (! $state instanceof State) {
            return null;
        }

        $link = AddressAreaStateLink::query()
            ->where('state_id', $state->getKey())
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

        $visited = [];

        while ($area instanceof AddressArea) {
            $areaId = (string) $area->getKey();

            if (isset($visited[$areaId])) {
                return null;
            }

            $visited[$areaId] = true;

            $stateId = AddressAreaStateLink::query()
                ->where('address_area_id', $areaId)
                ->value('state_id');

            if (is_string($stateId) && $stateId !== '') {
                return $stateId;
            }

            if (! is_string($area->parent_id) || $area->parent_id === '') {
                return null;
            }

            $area = AddressArea::query()->find($area->parent_id);
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
