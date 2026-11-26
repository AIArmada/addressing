<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface AddressAreaAliasDeleter
{
    public function execute(string $areaId, string $aliasId): void;
}
