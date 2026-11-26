<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface StateDatasetSeeder
{
    /**
     * @param  ?callable(string, int, int, ?string=): void  $progress
     * @return array<string, int>
     */
    public function execute(?array $states = null, ?callable $progress = null): array;
}
