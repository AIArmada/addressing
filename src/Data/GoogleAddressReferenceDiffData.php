<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

final readonly class GoogleAddressReferenceDiffData
{
    /**
     * @param  list<array{dimension: string, ours: string, theirs: string}>  $drifts
     */
    public function __construct(
        public string $countryCode,
        public array $drifts = [],
        public ?string $format = null,
        public bool $googleSilent = false,
    ) {}

    public function hasDrift(): bool
    {
        return $this->drifts !== [];
    }
}
