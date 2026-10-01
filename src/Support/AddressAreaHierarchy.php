<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Models\AddressArea;
use AIArmada\CommerceSupport\Support\LikeSearch;
use Illuminate\Support\Collection;

final class AddressAreaHierarchy
{
    /**
     * @return array<string, string>
     */
    public static function parentOptions(
        ?string $countryId,
        ?string $currentAreaId = null,
        ?string $search = null,
        ?int $limit = 5000,
    ): array {
        if ($countryId === null) {
            return [];
        }

        $query = AddressArea::query()
            ->where('country_id', $countryId)
            ->orderBy('name');

        if ($search !== null && mb_trim($search) !== '') {
            LikeSearch::whereLike($query, 'name', LikeSearch::contains(mb_trim($search)));
        }

        if ($limit !== null) {
            $query->limit(max(1, $limit));
        }

        $areas = $query->get(['id', 'name', 'parent_id']);

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

    /**
     * Validate a parent assignment against an explicit parent map.
     *
     * Unlike validateParentAssignment(), which walks stored rows, this sees
     * caller-supplied links such as rows staged by an import that are not in
     * the database yet. Ids missing from the map fall back to stored rows so
     * chains leaving the map keep the database-backed behavior. The chain is
     * walked for new and existing rows alike, and any repeated node fails the
     * row, including a pre-existing loop the record itself is not part of.
     *
     * @param  array<string, string|null>  $parentById  area id => parent id (null roots)
     */
    public static function validateParentAssignmentInGraph(?string $recordId, string $candidateParentId, array $parentById): ?string
    {
        if ($recordId !== null && $recordId === $candidateParentId) {
            return 'Selected parent area cannot be the current area.';
        }

        $visited = [];
        $currentId = $candidateParentId;

        while (true) {
            if ($currentId === $recordId) {
                return 'Selected parent area would create a hierarchy cycle.';
            }

            if (isset($visited[$currentId])) {
                return 'Selected parent area would create a hierarchy cycle.';
            }

            $visited[$currentId] = true;

            if (array_key_exists($currentId, $parentById)) {
                $nextId = $parentById[$currentId];
            } else {
                $nextId = AddressArea::query()->select(['id', 'parent_id'])->find($currentId)?->parent_id;
                $nextId = $nextId !== null ? (string) $nextId : null;
            }

            if ($nextId === null) {
                return null;
            }

            $currentId = $nextId;
        }
    }

    public static function validateParentCompatibility(AddressArea $parent, ?int $childLevel): ?string
    {
        if ($childLevel === null || $parent->level === null) {
            return null;
        }

        if ($parent->level >= $childLevel) {
            return 'The parent area must be at a lower level (' . $parent->level . ') than this area (' . $childLevel . ').';
        }

        return null;
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
