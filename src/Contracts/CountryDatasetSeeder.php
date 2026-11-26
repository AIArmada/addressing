<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface CountryDatasetSeeder
{
    /**
     * @param  ?callable(string, int, int, ?string=): void  $progress
     * @return array<string, int>
     */
    public function execute(?callable $progress = null): array;
}
