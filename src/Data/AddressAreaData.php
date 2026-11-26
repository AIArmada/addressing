<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

final readonly class AddressAreaData
{
    public function __construct(
        public string $source,
        public string $sourceId,
        public string $countryCode,
        public string $type,
        public string $name,
        public ?string $nativeName = null,
        public ?string $code = null,
        public ?string $parentSourceId = null,
        public ?int $level = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?string $hierarchyType = null,
        public string $relationshipType = 'contains',
        public array $metadata = [],
        public array $sourcePayload = [],
    ) {}
}
