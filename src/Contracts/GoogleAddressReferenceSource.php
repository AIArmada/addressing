<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface GoogleAddressReferenceSource
{
    /** @return array<string, mixed> */
    public function fetch(string $code): array;
}
