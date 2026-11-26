<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

final readonly class ExportResolutionGapAliasesResultData
{
    /**
     * @param  array<string, list<array{source_id: string, name: string, name_type: string, is_preferred: bool}>>  $candidatesByProvider
     * @param  list<string>  $warnings
     */
    public function __construct(
        public string $countryCode,
        public array $candidatesByProvider = [],
        public int $skippedUnshipped = 0,
        public int $skippedDeclared = 0,
        public int $skippedMissingAlias = 0,
        public int $skippedMissingArea = 0,
        public int $skippedDuplicates = 0,
        public int $pruned = 0,
        public array $warnings = [],
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
