<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

final class CldrSubdivisionDiffData
{
    /**
     * @param  list<array{country: string, dimension: string, code: string, ours: string, theirs: string}>  $drifts
     */
    public function __construct(
        public readonly ?string $countryCode,
        public readonly array $drifts = [],
        public readonly ?string $cldrVersion = null,
        public readonly int $nullCodeRows = 0,
    ) {}

    public function hasDrift(): bool
    {
        return $this->drifts !== [];
    }
}
