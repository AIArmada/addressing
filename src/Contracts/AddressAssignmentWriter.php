<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface AddressAssignmentWriter
{
    /**
     * @param  array<string, string|null>  $assignments
     * @param  array<string, mixed>  $metadata
     * @param  class-string|null  $addressModelClass
     */
    public function execute(string $addressId, array $assignments, ?string $stateId = null, array $metadata = [], ?string $addressModelClass = null): void;

    /**
     * @param  array<string, string|null>  $assignments
     * @return array<string, string>
     */
    public function validate(?string $countryCode, ?string $persistedStateId, array $assignments, ?string $stateId = null): array;

    public function pruneIncompatibleAssignments(string $addressId): void;

    /**
     * @param  array<string, string>  $assignments
     * @return array<string, string>
     */
    public function narrowToValidAssignments(?string $countryCode, ?string $persistedStateId, array $assignments): array;
}
