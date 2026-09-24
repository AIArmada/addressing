<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

final class AddressLineFilter
{
    /**
     * Drop only missing segments. Unlike bare array_filter(), the string
     * '0' is a present line, not an empty one.
     *
     * @param  array<?string>  $lines
     * @return array<string>
     */
    public static function present(array $lines): array
    {
        return array_filter(
            $lines,
            static fn (?string $line): bool => $line !== null && $line !== '',
        );
    }
}
