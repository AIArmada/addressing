<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Traits;

use AIArmada\Addressing\Support\SpatialLocation;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

trait HasSpatialLocation
{
    /**
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function withinRadius(Builder $query, float $latitude, float $longitude, float $meters): void
    {
        SpatialLocation::requireEnabled($query->getModel()->getConnection(), 'postgis');
        SpatialLocation::validateCoordinates($latitude, $longitude);

        if (! is_finite($meters) || $meters < 0) {
            throw new InvalidArgumentException('Radius must be a finite, non-negative number of meters.');
        }

        $location = SpatialLocation::column($query, 'location');
        $query->whereRaw("ST_DWithin({$location}, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography, ?)", [
            $longitude,
            $latitude,
            $meters,
        ]);
    }

    /**
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function inGeohash(Builder $query, string $prefix): void
    {
        SpatialLocation::requireEnabled($query->getModel()->getConnection(), 'geohash');

        if (preg_match('/\\A[0123456789bcdefghjkmnpqrstuvwxyz]{1,12}\\z/', $prefix) !== 1) {
            throw new InvalidArgumentException('Expected a lowercase geohash prefix of 1 to 12 characters.');
        }

        $column = $query->qualifyColumn('geohash');
        $query->where($column, '>=', $prefix)->where($column, '<', $prefix . '{');
    }

    /**
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function inH3Cell(Builder $query, float $latitude, float $longitude): void
    {
        SpatialLocation::requireEnabled($query->getModel()->getConnection(), 'h3');
        SpatialLocation::validateCoordinates($latitude, $longitude);

        $cell = SpatialLocation::column($query, 'h3_cell');
        $query->whereRaw("{$cell} = h3_latlng_to_cell(point(?, ?), 9)", [$longitude, $latitude]);
    }
}
