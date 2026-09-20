<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

final class ExportResolutionGapAliasesResultData
{
    /**
     * @param  array<string, list<array{source_id: string, name: string, name_type: string, is_preferred: bool}>>  $candidatesByProvider
     * @param  list<string>  $warnings
     */
    public function __construct(
        public readonly string $countryCode,
        public readonly array $candidatesByProvider = [],
        public readonly int $skippedUnshipped = 0,
        public readonly int $skippedDeclared = 0,
        public readonly int $skippedMissingAlias = 0,
        public readonly int $skippedMissingArea = 0,
        public readonly int $skippedDuplicates = 0,
        public readonly int $pruned = 0,
        public readonly array $warnings = [],
    ) {}

    public function candidateCount(): int
    {
        $count = 0;

        foreach ($this->candidatesByProvider as $candidates) {
            $count += count($candidates);
        }

        return $count;
    }
}
