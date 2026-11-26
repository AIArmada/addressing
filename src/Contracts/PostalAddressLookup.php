<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface PostalAddressLookup
{
    /** @return list<array<string, mixed>> */
    public function search(string $query): array;
}
