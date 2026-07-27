<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Models\AddressArea;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class SearchAddressAreasAction
{
    /**
     * @return Collection<int, AddressArea>
     */
    public function execute(
        string $query,
        ?string $countryCode = null,
        ?string $role = null,
        ?string $type = null,
        ?string $parentId = null,
        ?string $hierarchyType = null,
        ?string $postalCode = null,
        int $limit = 25,
    ): Collection {
        $query = mb_trim($query);

        if ($query === '') {
            return new Collection;
        }

        $needle = mb_strtolower($query);

        if ($hierarchyType !== null && mb_trim($hierarchyType) === '') {
            return new Collection;
        }

        return AddressArea::query()
            ->when($countryCode !== null, fn (Builder $builder): Builder => $builder->where('country_code', mb_strtoupper($countryCode)))
            ->when($type !== null, fn (Builder $builder): Builder => $builder->where('type', $type))
            ->when($parentId !== null && $hierarchyType === null, fn (Builder $builder): Builder => $builder->where('parent_id', $parentId))
            ->when($parentId !== null && $hierarchyType !== null, function (Builder $builder) use ($parentId, $hierarchyType): Builder {
                return $builder->whereHas('ancestors', function (Builder $relationshipQuery) use ($parentId, $hierarchyType): void {
                    $relationshipQuery
                        ->whereKey($parentId)
                        ->where(config('addressing.tables.area_relationships', 'address_area_relationships') . '.hierarchy_type', $hierarchyType);
                });
            })
            ->when($hierarchyType !== null && $parentId === null, function (Builder $builder) use ($hierarchyType): Builder {
                return $builder->where(function (Builder $hierarchyQuery) use ($hierarchyType): void {
                    $hierarchyQuery
                        ->whereHas('ancestors', fn (Builder $query): Builder => $query->where(config('addressing.tables.area_relationships', 'address_area_relationships') . '.hierarchy_type', $hierarchyType))
                        ->orWhereDoesntHave('ancestors');
                });
            })
            ->when($postalCode !== null, fn (Builder $builder): Builder => $builder->whereHas('postalCodes', fn (Builder $codes): Builder => $codes->where('code', mb_trim($postalCode))))
            ->when($role !== null, fn (Builder $builder): Builder => $builder->whereHas('roles', fn (Builder $roles): Builder => $roles->where('role', $role)))
            ->where(function (Builder $builder) use ($needle): void {
                if (mb_strlen($needle) < 3) {
                    $builder
                        ->whereRaw('LOWER(name) = ?', [$needle])
                        ->orWhereRaw('LOWER(slug) = ?', [$needle])
                        ->orWhereHas('names', fn (Builder $names): Builder => $names->whereRaw('LOWER(name) = ?', [$needle]));

                    return;
                }

                $builder
                    ->whereRaw('LOWER(name) LIKE ?', ["%{$needle}%"])
                    ->orWhereRaw('LOWER(slug) LIKE ?', ["%{$needle}%"])
                    ->orWhereHas('names', fn (Builder $names): Builder => $names->whereRaw('LOWER(name) LIKE ?', ["%{$needle}%"]));
            })
            ->orderByRaw('CASE WHEN LOWER(name) = ? THEN 0 WHEN LOWER(name) LIKE ? THEN 1 ELSE 2 END', [$needle, "{$needle}%"])
            ->orderBy('name')
            ->limit(max(1, min($limit, 100)))
            ->get();
    }
}
