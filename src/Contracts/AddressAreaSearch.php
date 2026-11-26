<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

use AIArmada\Addressing\Data\AddressAreaMatchData;

interface AddressAreaSearch
{
    /** @return list<AddressAreaMatchData> */
    public function execute(string $query, ?string $countryCode = null, ?string $role = null, ?string $type = null, ?string $parentId = null, ?string $hierarchyType = null, ?string $postalCode = null, int $limit = 25): array;
}
