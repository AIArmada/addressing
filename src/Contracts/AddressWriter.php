<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface AddressWriter
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, string|null>|null  $areaAssignments
     * @param  class-string|null  $modelClass
     * @param  array<string, mixed>  $assignmentMetadata
     */
    public function execute(array $data, ?array $areaAssignments, ?string $recordId = null, ?string $modelClass = null, array $assignmentMetadata = []): string;
}
