<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface CityDatasetSeeder
{
    /**
     * @param  ?callable(string, int, int, ?string=): void  $progress
     * @return array<string, int>
     */
    public function execute(?array $cities = null, ?callable $progress = null): array;
}
