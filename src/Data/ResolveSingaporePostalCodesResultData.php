<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

final readonly class ResolveSingaporePostalCodesResultData
{
    /**
     * @param  list<string>  $resolved
     * @param  list<string>  $invalid
     */
    public function __construct(
        public array $resolved = [],
        public array $invalid = [],
    ) {}
}
