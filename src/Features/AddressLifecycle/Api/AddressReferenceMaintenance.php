<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\AddressLifecycle\Api;

interface AddressReferenceMaintenance
{
    public function clearState(string $stateId): void;

    public function clearCity(string $cityId): void;

    public function clearArea(string $areaId): void;
}
