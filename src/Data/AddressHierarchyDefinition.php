<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

final readonly class AddressHierarchyDefinition
{
    /**
     * @param  list<AddressLevelDefinition>  $levels
     */
    public function __construct(
        public string $key,
        public string $label,
        public array $levels,
    ) {}
}
