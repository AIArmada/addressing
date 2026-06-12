<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

class ImportAddressAreaFailureData
{
    public function __construct(
        public readonly string $sourceId,
        public readonly string $reason,
        public readonly ?string $name = null,
    ) {}
}
