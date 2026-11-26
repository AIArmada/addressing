<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\Geography\Api;

interface AreaAliasMatcher
{
    public function match(string $areaId, string $countryCode, string $role, string $value, string $normalized): void;
}
