<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface CountryLinkSeeder
{
    /**
     * @param  ?callable(string, int, int, ?string=): void  $progress
     * @return array{currency_links: int, timezone_links: int}
     */
    public function execute(?callable $progress = null): array;
}
