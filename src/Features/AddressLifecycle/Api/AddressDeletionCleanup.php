<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\AddressLifecycle\Api;

interface AddressDeletionCleanup
{
    public function execute(string $addressId): void;
}
