<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

final class GoogleAddressReferenceDiffData
{
    /**
     * @param  list<array{dimension: string, ours: string, theirs: string}>  $drifts
     */
    public function __construct(
        public readonly string $countryCode,
        public readonly array $drifts = [],
        public readonly ?string $format = null,
        public readonly bool $googleSilent = false,
    ) {}

    public function hasDrift(): bool
    {
        return $this->drifts !== [];
    }
}
