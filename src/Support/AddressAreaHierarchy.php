<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Models\AddressArea;
use Illuminate\Support\Collection;

final class AddressAreaHierarchy
{
    /**
     * @return array<string, string>
     */
    public static function parentOptions(?string $countryId, ?string $currentAreaId = null): array
    {
        if ($countryId === null) {
            return [];
        }

        $areas = AddressArea::query()
            ->where('country_id', $countryId)
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        if ($currentAreaId === null) {
            return $areas
                ->mapWithKeys(static fn (AddressArea $area): array => [
                    (string) $area->getKey() => $area->name,
                ])
                ->toArray();
        }

        $areasById = $areas->keyBy(static fn (AddressArea $area): string => (string) $area->getKey());

        return $areas
            ->filter(
                static fn (AddressArea $area): bool => ! self::wouldCreateCycleFromCollection(
                    $areasById,
                    $currentAreaId,
                    (string) $area->getKey(),
                ),
            )
            ->mapWithKeys(static fn (AddressArea $area): array => [
                (string) $area->getKey() => $area->name,
            ])
            ->toArray();
    }

    public static function validateParentAssignment(?AddressArea $record, AddressArea $candidateParent): ?string
    {
        if ($record === null) {
            return null;
        }

        if ((string) $record->getKey() === (string) $candidateParent->getKey()) {
            return 'Selected parent area cannot be the current area.';
        }

        if (! self::wouldCreateCycle($record, $candidateParent)) {
            return null;
        }

        return 'Selected parent area would create a hierarchy cycle.';
    }

    private static function wouldCreateCycle(AddressArea $record, AddressArea $candidateParent): bool
    {
        $visited = [];
        $current = $candidateParent;

        while (true) {
            $currentId = (string) $current->getKey();

            if (isset($visited[$currentId])) {
                return true;
            }

            $visited[$currentId] = true;

            if ($currentId === (string) $record->getKey()) {
                return true;
            }

            $parentId = $current->parent_id;

            if ($parentId === null) {
                return false;
            }

            $current = AddressArea::query()
                ->select(['id', 'parent_id'])
                ->find($parentId);

            if (! $current instanceof AddressArea) {
                return false;
            }
        }
    }

    private static function wouldCreateCycleFromCollection(
        Collection $areasById,
        string $currentAreaId,
        string $candidateParentId,
    ): bool {
        $visited = [];
        $current = $areasById->get($candidateParentId);

        if (! $current instanceof AddressArea) {
            return false;
        }

        while (true) {
            $currentId = (string) $current->getKey();

            if (isset($visited[$currentId])) {
                return true;
            }

            $visited[$currentId] = true;

            if ($currentId === $currentAreaId) {
                return true;
            }

            $parentId = $current->parent_id;

            if ($parentId === null) {
                return false;
            }

            $current = $areasById->get((string) $parentId);

            if (! $current instanceof AddressArea) {
                return false;
            }
        }
    }
}
