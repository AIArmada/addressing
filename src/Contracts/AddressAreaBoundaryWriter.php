<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface AddressAreaBoundaryWriter
{
    public function execute(string $areaId, ?string $wkt): void;
}
