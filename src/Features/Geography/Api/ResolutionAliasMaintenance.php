<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\Geography\Api;

interface ResolutionAliasMaintenance
{
    public function pruneDuplicates(string $countryCode): int;
}
