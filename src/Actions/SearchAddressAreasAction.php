<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Models\AddressArea;
use AIArmada\Addressing\Support\AddressingTableResolver;
use AIArmada\CommerceSupport\Support\LikeSearch;
use Carbon\CarbonImmutable;
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
        $escapedNeedle = LikeSearch::escape($needle);
        $containsPattern = "%{$escapedNeedle}%";
        $prefixPattern = "{$escapedNeedle}%";

        if ($hierarchyType !== null && mb_trim($hierarchyType) === '') {
            return new Collection;
        }

        $escapeClause = LikeSearch::escapeClause(AddressArea::query()->getConnection());

        return AddressArea::query()
            ->where('is_active', true)
            ->when($countryCode !== null, fn (Builder $builder): Builder => $builder->where('country_code', mb_strtoupper($countryCode)))
            ->when($type !== null, fn (Builder $builder): Builder => $builder->where('type', $type))
            ->when($parentId !== null && $hierarchyType === null, fn (Builder $builder): Builder => $builder->where('parent_id', $parentId))
            ->when($parentId !== null && $hierarchyType !== null, function (Builder $builder) use ($parentId, $hierarchyType): Builder {
                return $builder->whereHas('ancestors', function (Builder $relationshipQuery) use ($parentId, $hierarchyType): void {
                    $relationshipQuery
                        ->whereKey($parentId)
                        ->where(AddressingTableResolver::resolve('area_relationships') . '.hierarchy_type', $hierarchyType)
                        ->where(AddressingTableResolver::resolve('area_relationships') . '.relationship_type', 'contains')
                        ->where(function (Builder $query): void {
                            $query->whereNull('valid_from')->orWhereDate('valid_from', '<=', CarbonImmutable::now());
                        })
                        ->where(function (Builder $query): void {
                            $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', CarbonImmutable::now());
                        });
                });
            })
            ->when($hierarchyType !== null && $parentId === null, function (Builder $builder) use ($hierarchyType): Builder {
                return $builder->where(function (Builder $hierarchyQuery) use ($hierarchyType): void {
                    $hierarchyQuery
                        ->whereHas('ancestors', function (Builder $query) use ($hierarchyType): void {
                            $query
                                ->where(AddressingTableResolver::resolve('area_relationships') . '.hierarchy_type', $hierarchyType)
                                ->where(AddressingTableResolver::resolve('area_relationships') . '.relationship_type', 'contains')
                                ->where(function (Builder $relationshipQuery): void {
                                    $relationshipQuery->whereNull('valid_from')->orWhereDate('valid_from', '<=', CarbonImmutable::now());
                                })
                                ->where(function (Builder $relationshipQuery): void {
                                    $relationshipQuery->whereNull('valid_until')->orWhereDate('valid_until', '>=', CarbonImmutable::now());
                                });
                        })
                        ->orWhereDoesntHave('ancestors');
                });
            })
            ->when($postalCode !== null, fn (Builder $builder): Builder => $builder->whereHas('postalCodes', fn (Builder $codes): Builder => $codes->where('code', mb_trim($postalCode))))
            ->when($role !== null, fn (Builder $builder): Builder => $builder->whereHas('roles', fn (Builder $roles): Builder => $roles->where('role', $role)))
            ->where(function (Builder $builder) use ($containsPattern, $needle, $escapeClause): void {
                if (mb_strlen($needle) < 3) {
                    $builder
                        ->whereRaw('LOWER(name) = ?', [$needle])
                        ->orWhereRaw('LOWER(slug) = ?', [$needle])
                        ->orWhereHas('names', fn (Builder $names): Builder => $names->whereRaw('LOWER(name) = ?', [$needle]));

                    return;
                }

                $builder
                    ->whereRaw("LOWER(name) LIKE ? {$escapeClause}", [$containsPattern])
                    ->orWhereRaw("LOWER(slug) LIKE ? {$escapeClause}", [$containsPattern])
                    ->orWhereHas('names', fn (Builder $names): Builder => $names->whereRaw("LOWER(name) LIKE ? {$escapeClause}", [$containsPattern]));
            })
            ->orderByRaw("CASE WHEN LOWER(name) = ? THEN 0 WHEN LOWER(name) LIKE ? {$escapeClause} THEN 1 ELSE 2 END", [$needle, $prefixPattern])
            ->orderBy('name')
            ->limit(max(1, min($limit, 100)))
            ->get();
    }
}
