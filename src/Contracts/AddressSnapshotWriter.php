<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

use AIArmada\Addressing\Data\AddressData;

interface AddressSnapshotWriter
{
    public function execute(string $snapshotableType, string | int $snapshotableId, string | AddressData $address, ?string $reason = null, ?string $label = null): string;
}
