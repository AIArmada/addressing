<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\Geography\Actions;

use AIArmada\Addressing\Features\Geography\Api\AreaStateLookup;
use AIArmada\Addressing\Features\Geography\Support\AddressAreaStateBridge;

final readonly class ResolveAreaStateAction implements AreaStateLookup
{
    public function areaIdForState(?string $stateId, ?string $hierarchyType = null): ?string
    {
        return AddressAreaStateBridge::areaIdForState($stateId, $hierarchyType);
    }

    public function stateIdForArea(?string $areaId): ?string
    {
        return AddressAreaStateBridge::stateIdForArea($areaId);
    }
}
