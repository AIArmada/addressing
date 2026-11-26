<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Persistence;

use Illuminate\Database\Connection;
use InvalidArgumentException;

final class ReadOnlyReferenceSnapshot
{
    /**
     * @template T
     *
     * @param  callable(): T  $read
     * @return T
     */
    public static function run(Connection $connection, callable $read): mixed
    {
        if ($connection->getDriverName() === 'pgsql' && $connection->transactionLevel() > 0) {
            throw new InvalidArgumentException('PostgreSQL reference exports cannot run inside an existing transaction because the snapshot boundary must be established before any queries.');
        }

        return $connection->transaction(static function () use ($connection, $read): mixed {
            if ($connection->getDriverName() === 'pgsql') {
                $connection->statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ READ ONLY');
            }

            return $read();
        });
    }
}
