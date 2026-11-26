<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

final readonly class ImportPostalCodesResultData
{
    /** @var list<ImportPostalCodeFailureData> */
    public array $failures;

    /**
     * @param  list<ImportPostalCodeFailureData>  $failures
     */
    public function __construct(
        public int $created = 0,
        public int $updated = 0,
        public int $skipped = 0,
        array $failures = [],
    ) {
        $this->failures = $failures;
    }

    public function hasFailures(): bool
    {
        return $this->failures !== [];
    }
}
