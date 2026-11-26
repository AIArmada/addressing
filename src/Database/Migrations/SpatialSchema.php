<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Database\Migrations;

use AIArmada\Addressing\Support\AddressingTableResolver;
use Illuminate\Database\Connection;
use LogicException;

final class SpatialSchema
{
    private const string ValidCoordinates = 'latitude BETWEEN -90 AND 90 AND longitude BETWEEN -180 AND 180';

    public static function postgis(Connection $connection): void
    {
        self::requireExtension($connection, 'postgis');

        foreach (['addresses', 'areas'] as $tableKey) {
            self::addColumn($connection, $tableKey, 'location', 'geography(Point, 4326)', 'GIST', 'ST_SetSRID(ST_MakePoint(longitude, latitude), 4326)::geography');
        }

        self::addColumn($connection, 'areas', 'boundary', 'geometry(MultiPolygon, 4326)', 'GIST');
    }

    public static function geohash(Connection $connection): void
    {
        self::requireExtension($connection, 'postgis');

        foreach (['addresses', 'areas'] as $tableKey) {
            self::addColumn($connection, $tableKey, 'geohash', 'varchar(12) COLLATE "C"', 'BTREE', 'ST_GeoHash(ST_SetSRID(ST_MakePoint(longitude, latitude), 4326), 12)');
        }
    }

    public static function h3(Connection $connection): void
    {
        self::requireExtension($connection, 'h3');

        foreach (['addresses', 'areas'] as $tableKey) {
            self::addColumn($connection, $tableKey, 'h3_cell', 'h3index', 'BTREE', 'h3_latlng_to_cell(point(longitude, latitude), 9)');
        }
    }

    private static function requireExtension(Connection $connection, string $extension): void
    {
        if ($connection->getDriverName() !== 'pgsql') {
            throw new LogicException('Addressing spatial migrations require PostgreSQL.');
        }

        if ($connection->pretending()) {
            return;
        }

        if (! $connection->scalar('SELECT EXISTS (SELECT 1 FROM pg_extension WHERE extname = ?)', [$extension], false)) {
            throw new LogicException("Install the PostgreSQL [{$extension}] extension before running the addressing spatial migration.");
        }
    }

    private static function addColumn(
        Connection $connection,
        string $tableKey,
        string $column,
        string $type,
        string $indexMethod,
        ?string $expression = null,
    ): void {
        $grammar = $connection->getQueryGrammar();
        $table = $grammar->wrapTable(AddressingTableResolver::resolve($tableKey));
        $index = $grammar->wrap('addr_' . $column . '_' . mb_substr(hash('sha256', $table), 0, 12));
        $column = $grammar->wrap($column);
        $definition = $expression === null
            ? $type
            : $type . ' GENERATED ALWAYS AS (CASE WHEN ' . self::ValidCoordinates . " THEN {$expression} END) STORED";

        $connection->statement("ALTER TABLE {$table} ADD COLUMN IF NOT EXISTS {$column} {$definition}");
        $connection->statement("CREATE INDEX IF NOT EXISTS {$index} ON {$table} USING {$indexMethod} ({$column})");
    }
}
