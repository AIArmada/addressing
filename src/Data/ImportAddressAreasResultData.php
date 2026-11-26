<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

final readonly class ImportAddressAreasResultData
{
    /** @var array<int, ImportAddressAreaFailureData> */
    public array $failures;

    public function __construct(
        public int $created = 0,
        public int $updated = 0,
        public int $skipped = 0,
        array $failures = [],
    ) {
        $this->failures = $failures;
    }

    public function totalProcessed(): int
    {
        return $this->created + $this->updated + $this->skipped + count($this->failures);
    }

    public function hasFailures(): bool
    {
        return $this->failures !== [];
    }
}
