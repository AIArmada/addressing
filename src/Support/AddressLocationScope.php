<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Data\AddressLocationData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class AddressLocationScope
{
    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query, AddressLocationData $location, string $relation = 'addresses'): Builder
    {
        $criteria = $location->criteria();
        $assignments = $location->assignments();

        if ($criteria === [] && $assignments === []) {
            return $query;
        }

        return $query->whereHas($relation, function (Builder $addressQuery) use ($criteria, $assignments): void {
            foreach ($criteria as $column => $value) {
                $addressQuery->where($column, $value);
            }

            foreach ($assignments as $role => $areaId) {
                $addressQuery->whereHas('areaAssignments', function (Builder $assignmentQuery) use ($role, $areaId): void {
                    $assignmentQuery
                        ->where('role', $role)
                        ->where('address_area_id', $areaId)
                        ->where('is_primary', true);
                });
            }
        });
    }
}
