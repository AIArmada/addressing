<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

final class ImportPostalCodesResultData
{
    /** @var list<ImportPostalCodeFailureData> */
    public readonly array $failures;

    /**
     * @param  list<ImportPostalCodeFailureData>  $failures
     */
    public function __construct(
        public readonly int $created = 0,
        public readonly int $updated = 0,
        public readonly int $skipped = 0,
        array $failures = [],
    ) {
        $this->failures = $failures;
    }

    public function hasFailures(): bool
    {
        return $this->failures !== [];
    }
}
