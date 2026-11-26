<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface AddressAreaAliasWriter
{
    /** @param array{name: mixed, name_type?: mixed, is_preferred?: mixed} $attributes */
    public function handle(string $areaId, array $attributes, ?string $aliasId = null): string;
}
