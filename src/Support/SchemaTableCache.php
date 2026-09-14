<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use Illuminate\Support\Facades\Schema;

final class SchemaTableCache
{
    private const string REQUEST_CACHE_KEY = 'aiarmada.addressing.schema-tables';

    public static function exists(string $table): bool
    {
        if (app()->bound('request')) {
            $cache = request()->attributes->get(self::REQUEST_CACHE_KEY, []);

            if (is_array($cache) && array_key_exists($table, $cache)) {
                return (bool) $cache[$table];
            }

            $exists = Schema::hasTable($table);
            $cache[$table] = $exists;
            request()->attributes->set(self::REQUEST_CACHE_KEY, $cache);

            return $exists;
        }

        return Schema::hasTable($table);
    }
}
