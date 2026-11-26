<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface AddressingDatasetSeeder
{
    /**
     * @param  ?callable(string, int, int, ?string=): void  $progress
     * @return array{countries: array<string, mixed>, references: array<string, mixed>, states: array<string, mixed>, cities: array<string, mixed>, geographies: array<string, mixed>, postal_codes: array{seeded: list<string>, skipped: list<string>, codes: array<string, array{created: int, updated: int, skipped: int, links: int}>}}
     */
    public function execute(?callable $progress = null): array;
}
