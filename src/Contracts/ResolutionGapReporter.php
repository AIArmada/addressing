<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface ResolutionGapReporter
{
    /** @param array<string, mixed>|null $context */
    public function report(string $source, string $countryCode, string $role, string $value, string $reason = 'unmatched', ?array $context = null): string;
}
