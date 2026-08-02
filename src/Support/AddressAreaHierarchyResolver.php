<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Models\AddressArea;
use AIArmada\Addressing\Models\AddressAreaRelationship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class AddressAreaHierarchyResolver
{
    /** @var array<string, Collection<int, AddressArea>> */
    private array $ancestorCache = [];

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
        $cacheKey = (string) $area->getKey() . ':' . $hierarchyType;

        if (array_key_exists($cacheKey, $this->ancestorCache)) {
            return $this->ancestorCache[$cacheKey];
        }

        $ancestors = new Collection;
        $pending = [(string) $area->getKey()];
        $visited = [(string) $area->getKey() => true];

        while ($pending !== []) {
            $relationships = AddressAreaRelationship::query()
                ->whereIn('child_address_area_id', $pending)
                ->where('relationship_type', 'contains')
                ->where('hierarchy_type', $hierarchyType)
                ->where(function ($query): void {
                    $query->whereNull('valid_from')->orWhereDate('valid_from', '<=', now());
                })
                ->where(function ($query): void {
                    $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', now());
                })
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->get(['parent_address_area_id', 'child_address_area_id']);

            $parentIds = $relationships
                ->pluck('parent_address_area_id')
                ->map(static fn (mixed $id): string => (string) $id)
                ->unique()
                ->filter(static fn (string $id): bool => ! isset($visited[$id]))
                ->values();

            if ($parentIds->isEmpty()) {
                break;
            }

            $parents = AddressArea::query()
                ->whereIn('id', $parentIds->all())
                ->where('is_active', true)
                ->get()
                ->keyBy(fn (AddressArea $parent): string => (string) $parent->getKey());

            $pending = [];

            foreach ($parentIds as $parentId) {
                $visited[$parentId] = true;
                $parent = $parents->get($parentId);

                if ($parent instanceof AddressArea) {
                    $ancestors->push($parent);
                    $pending[] = $parentId;
                }
            }
        }

        return $this->ancestorCache[$cacheKey] = $ancestors;
    }

    private function hasAncestor(AddressArea $area, string $ancestorId, string $hierarchyType): bool
    {
        return $this->ancestorsOf($area, $hierarchyType)
            ->contains(fn (AddressArea $ancestor): bool => (string) $ancestor->getKey() === $ancestorId);
    }
}
