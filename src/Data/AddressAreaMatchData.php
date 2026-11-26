<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

final readonly class AddressAreaMatchData
{
    public function __construct(
        public string $id,
        public ?string $countryId,
        public string $countryCode,
        public ?string $parentId,
        public string $type,
        public ?int $level,
        public string $name,
        public ?string $nativeName,
        public ?string $code,
        public string $slug,
        public string $source,
        public string $sourceId,
        public ?float $latitude,
        public ?float $longitude,
    ) {}
}
