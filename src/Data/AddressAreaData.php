<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

class AddressAreaData
{
    public function __construct(
        public readonly string $source,
        public readonly string $sourceId,
        public readonly string $countryCode,
        public readonly string $type,
        public readonly string $name,
        public readonly ?string $nativeName = null,
        public readonly ?string $code = null,
        public readonly ?string $parentSourceId = null,
        public readonly ?int $level = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly array $metadata = [],
        public readonly array $sourcePayload = [],
    ) {}
}
