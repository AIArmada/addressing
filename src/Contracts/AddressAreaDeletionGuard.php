<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface AddressAreaDeletionGuard
{
    public function handle(string $key): void;
}
