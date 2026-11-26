<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use Illuminate\Database\Eloquent\Model;

final class SchemaTableCache
{
    public static function exists(Model $model): bool
    {
        return RequestCache::remember(
            'schema-tables',
            $model,
            'exists',
            static fn (): bool => $model->getConnection()->getSchemaBuilder()->hasTable($model->getTable()),
        );
    }
}
