<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use Illuminate\Support\Facades\Schema;

/**
 * Creates functional LOWER(column) indexes so case-insensitive name lookups
 * stay index-backed on every supported driver.
 *
 * Used by the package migrations only; runtime code queries with LOWER()
 * predicates and relies on these indexes existing.
 */
final class LowerIndex
{
    public static function create(string $table, string $column, string $index): void
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();
        $prefixedTable = $connection->getTablePrefix() . $table;

        $sql = match ($driver) {
            'mysql', 'mariadb' => sprintf(
                'CREATE INDEX `%s` ON `%s` ((LOWER(`%s`)))',
                $index,
                $prefixedTable,
                $column,
            ),
            default => sprintf(
                'CREATE INDEX "%s" ON "%s" (LOWER("%s"))',
                $index,
                $prefixedTable,
                $column,
            ),
        };

        $connection->statement($sql);
    }
}
