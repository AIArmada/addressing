<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface CountryGeographySeeder
{
    /**
     * @param  ?callable(string, int, int, ?string=): void  $progress
     * @return array{seeded: list<string>, skipped: list<string>, areas: array}
     */
    public function execute(?string $countryCode = null, ?callable $progress = null): array;
}
