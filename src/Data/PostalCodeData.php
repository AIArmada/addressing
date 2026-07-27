<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

final readonly class PostalCodeData
{
    public function __construct(
        public string $source,
        public string $sourceId,
        public string $countryCode,
        public string $code,
        public ?string $areaSource = null,
        public ?string $areaSourceId = null,
        public string $relationshipType = 'served_by',
        public bool $isPrimary = false,
        public array $metadata = [],
    ) {}
}
