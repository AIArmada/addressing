<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface AddressCountryDeletionGuard
{
    public function handle(string $key): void;
}
