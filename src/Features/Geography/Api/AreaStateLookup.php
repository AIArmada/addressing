<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\Geography\Api;

interface AreaStateLookup
{
    public function areaIdForState(?string $stateId, ?string $hierarchyType = null): ?string;

    public function stateIdForArea(?string $areaId): ?string;
}
