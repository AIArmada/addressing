<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface ResolutionGapCuration
{
    public function match(string $gapId, string $areaId, ?string $actor = null): void;

    public function ignore(string $gapId): void;
}
