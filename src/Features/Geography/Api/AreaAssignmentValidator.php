<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\Geography\Api;

interface AreaAssignmentValidator
{
    /**
     * @param  array<string, string|null>  $assignments
     * @return array<string, string>
     */
    public function validate(?string $countryCode, ?string $persistedStateId, array $assignments, ?string $stateId = null): array;

    /**
     * @param  array<string, string>  $assignments
     * @return array<string, string>
     */
    public function narrowToValidAssignments(?string $countryCode, ?string $persistedStateId, array $assignments): array;
}
