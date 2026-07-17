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

        if ($criteria === []) {
            return $query;
        }

        return $query->whereHas($relation, function (Builder $addressQuery) use ($criteria): void {
            foreach ($criteria as $column => $value) {
                $addressQuery->where($column, $value);
            }
        });
    }
}
