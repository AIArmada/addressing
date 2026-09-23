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
        /**
         * Assignment role that refines this level across hierarchies.
         *
         * Region-parented levels with a refinedBy narrow to the selected
         * refining role when stored links prove it (a picked district
         * narrows postal localities to its own rows), falling back to the
         * declared state parent otherwise.
         */
        public ?string $refinedBy = null,
    ) {}
}
