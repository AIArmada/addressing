<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

final readonly class AddressLevelDefinition
{
    public function __construct(
        public string $key,
        public string $label,
        public string $kind,
        public ?string $hierarchyType = null,
        public ?string $areaType = null,
        /** @var list<string> */
        public array $areaTypes = [],
        public ?int $areaLevel = null,
        /** @var list<int> */
        public array $areaLevels = [],
        public ?string $parentKey = null,
        public ?string $assignmentRole = null,
    ) {}
}
