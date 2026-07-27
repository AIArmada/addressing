<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

final readonly class ImportPostalCodeFailureData
{
    public function __construct(
        public string $sourceId,
        public string $reason,
        public ?string $code = null,
    ) {}
}
