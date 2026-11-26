<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface PostalCodeSeeder
{
    /**
     * @param  ?callable(string, int, int, ?string=): void  $progress
     * @return array{seeded: list<string>, skipped: list<string>, codes: array<string, array{created: int, updated: int, skipped: int, links: int}>}
     */
    public function execute(?string $countryCode = null, ?callable $progress = null): array;
}
