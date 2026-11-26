<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * Request-scoped memoization stored on the request attributes bag.
 *
 * Long-lived workers reuse container singletons across requests, so lookup
 * results must never live on service instances. Buckets keep unrelated
 * caches apart; without a bound request the callback runs uncached.
 */
final class RequestCache
{
    /**
     * Only non-null results are memoized: a miss must stay retryable when
     * reference data is seeded later in the same request.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public static function remember(string $bucket, Model $model, string $key, callable $callback): mixed
    {
        if (! app()->bound('request')) {
            return $callback();
        }

        $connection = $model->getConnection();
        $key = serialize([
            $model::class,
            $connection->getName(),
            $connection->getDatabaseName(),
            $connection->getTablePrefix(),
            $model->getTable(),
            $key,
        ]);

        $attribute = "aiarmada.addressing.{$bucket}";
        $cache = request()->attributes->get($attribute, []);

        if (is_array($cache) && array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        $value = $callback();

        if ($value !== null) {
            $cache[$key] = $value;
            request()->attributes->set($attribute, $cache);
        }

        return $value;
    }

    public static function forget(string $bucket): void
    {
        if (app()->bound('request')) {
            request()->attributes->remove("aiarmada.addressing.{$bucket}");
        }
    }
}
