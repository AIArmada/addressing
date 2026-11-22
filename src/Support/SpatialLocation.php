<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use LogicException;

final class SpatialLocation
{
    public static function requireEnabled(Connection $connection, string $feature): void
    {
        if (config("addressing.features.spatial.{$feature}") !== true) {
            throw new LogicException("Enable [addressing.features.spatial.{$feature}] to use this spatial operation.");
        }

        if ($connection->getDriverName() !== 'pgsql') {
            throw new LogicException('Addressing spatial operations require PostgreSQL.');
        }
    }

    public static function validateCoordinates(float $latitude, float $longitude): void
    {
        if (! is_finite($latitude) || ! is_finite($longitude) || abs($latitude) > 90 || abs($longitude) > 180) {
            throw new InvalidArgumentException('Expected latitude and longitude in WGS 84 degrees.');
        }
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     */
    public static function column(Builder $query, string $column): string
    {
        return $query->getQuery()->getGrammar()->wrap($query->qualifyColumn($column));
    }
}
