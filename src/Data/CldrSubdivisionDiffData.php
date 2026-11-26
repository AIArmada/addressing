<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

final readonly class CldrSubdivisionDiffData
{
    /**
     * @param  list<array{country: string, dimension: string, code: string, ours: string, theirs: string}>  $drifts
     */
    public function __construct(
        public ?string $countryCode,
        public array $drifts = [],
        public ?string $cldrVersion = null,
        public int $nullCodeRows = 0,
    ) {}

    public function hasDrift(): bool
    {
        return $this->drifts !== [];
    }
}
