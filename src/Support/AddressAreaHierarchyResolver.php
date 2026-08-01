<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Models\AddressArea;
use AIArmada\Addressing\Models\AddressAreaRelationship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class AddressAreaHierarchyResolver
{
    /**
     * Resolve an exact area name anywhere below a typed hierarchy root.
     *
     * @param  list<string>  $types
     */
    public function resolveWithinHierarchy(
        ?string $name,
        ?string $countryId,
        ?string $hierarchyRootId,
        string $hierarchyType,
        array $types,
    ): ?AddressArea {
        if (! filled($name) || $hierarchyRootId === null || $types === []) {
            return null;
        }

        $normalizedName = mb_strtolower(mb_trim($name));

        /** @var Collection<int, AddressArea> $matches */
        $matches = AddressArea::query()
            ->when($countryId !== null, fn (Builder $query): Builder => $query->where('country_id', $countryId))
            ->whereIn('type', $types)
            ->whereRaw('LOWER(name) = ?', [$normalizedName])
            ->get();

        $matches = $matches
            ->filter(fn (AddressArea $area): bool => $this->hasAncestor($area, $hierarchyRootId, $hierarchyType))
            ->values();

        return $matches->count() === 1 ? $matches->first() : null;
    }

    /**
     * Resolve an exact area name below a hierarchy root through a provider role.
     */
    public function resolveRoleWithinHierarchy(
        ?string $name,
        ?string $countryId,
        ?string $hierarchyRootId,
        string $hierarchyType,
        string $role,
    ): ?AddressArea {
        if (! filled($name) || $hierarchyRootId === null || $role === '') {
            return null;
        }

        $normalizedName = mb_strtolower(mb_trim($name));

        /** @var Collection<int, AddressArea> $matches */
        $matches = AddressArea::query()
            ->when($countryId !== null, fn (Builder $query): Builder => $query->where('country_id', $countryId))
            ->whereRaw('LOWER(name) = ?', [$normalizedName])
            ->whereHas('roles', fn (Builder $query): Builder => $query->where('role', $role))
            ->get()
            ->filter(fn (AddressArea $area): bool => $this->hasAncestor($area, $hierarchyRootId, $hierarchyType))
            ->values();

        return $matches->count() === 1 ? $matches->first() : null;
    }

    /**
     * Resolve the nearest matching typed ancestor in a hierarchy.
     *
     * @param  list<string>  $types
     */
    public function ancestorOfTypes(
        ?AddressArea $area,
        array $types,
        string $hierarchyType,
    ): ?AddressArea {
        if (! $area instanceof AddressArea || $types === []) {
            return null;
        }

        return $this->ancestorsOf($area, $hierarchyType)
            ->first(fn (AddressArea $ancestor): bool => in_array($ancestor->type, $types, true));
    }

    /**
     * Return all ancestors from nearest to furthest for any provider hierarchy.
     *
     * @return Collection<int, AddressArea>
     */
    public function ancestorsOf(AddressArea $area, string $hierarchyType): Collection
    {
        $ancestors = new Collection;
        $current = $area;
        $visited = [];

        while (is_string($current->parent_id) && $current->parent_id !== '') {
            if (in_array($current->parent_id, $visited, true)) {
                break;
            }

            $visited[] = $current->parent_id;
            $current = $this->parentFor($current, $hierarchyType);

            if (! $current instanceof AddressArea) {
                break;
            }

            $ancestors->push($current);
        }

        return $ancestors;
    }

    private function hasAncestor(AddressArea $area, string $ancestorId, string $hierarchyType): bool
    {
        return $this->ancestorsOf($area, $hierarchyType)
            ->contains(fn (AddressArea $ancestor): bool => (string) $ancestor->getKey() === $ancestorId);
    }

    private function parentFor(AddressArea $area, string $hierarchyType): ?AddressArea
    {
        $parentId = $area->parent_id;

        if (! is_string($parentId) || $parentId === '') {
            return null;
        }

        $relationshipExists = AddressAreaRelationship::query()
            ->where('parent_address_area_id', $parentId)
            ->where('child_address_area_id', $area->getKey())
            ->where('relationship_type', 'contains')
            ->where('hierarchy_type', $hierarchyType)
            ->exists();

        return $relationshipExists
            ? AddressArea::query()->find($parentId)
            : null;
    }
}
