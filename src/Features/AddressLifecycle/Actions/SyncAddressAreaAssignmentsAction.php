<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\AddressLifecycle\Actions;

use AIArmada\Addressing\Contracts\AddressAssignmentWriter;
use AIArmada\Addressing\Features\AddressLifecycle\Support\AddressOwnerGuard;
use AIArmada\Addressing\Features\Geography\Api\AreaAssignmentValidator;
use AIArmada\Addressing\Support\ModelResolver;
use RuntimeException;

final readonly class SyncAddressAreaAssignmentsAction implements AddressAssignmentWriter
{
    public function __construct(
        private AreaAssignmentValidator $validator,
    ) {}

    /**
     * @param  array<string, string|null>  $assignments
     * @param  array<string, mixed>  $metadata
     * @param  class-string|null  $addressModelClass
     */
    public function execute(
        string $addressId,
        array $assignments,
        ?string $stateId = null,
        array $metadata = [],
        ?string $addressModelClass = null,
    ): void {
        $addressClass = $addressModelClass ?? ModelResolver::addressClass();

        (new $addressClass)->getConnection()->transaction(function () use ($addressId, $assignments, $stateId, $metadata, $addressClass): void {
            $address = AddressOwnerGuard::lockAddressForWrite($addressId, $addressClass);
            $selectedAssignments = $assignments === [] ? [] : $this->validate($address->country_code, $address->state_id, $assignments, $stateId);
            ModelResolver::areaAssignmentClass()::query()
                ->where('address_id', $address->getKey())
                ->delete();

            $assignmentClass = ModelResolver::areaAssignmentClass();

            foreach ($selectedAssignments as $role => $areaId) {
                $assignment = new $assignmentClass;
                $assignment->fill([
                    'address_id' => $address->getKey(),
                    'address_area_id' => $areaId,
                    'role' => $role,
                    'is_primary' => true,
                    'metadata' => $metadata !== [] ? $metadata : null,
                ]);

                if (! $assignment->save()) {
                    throw new RuntimeException('The address area assignment could not be saved.');
                }
            }
        });
    }

    public function validate(?string $countryCode, ?string $persistedStateId, array $assignments, ?string $stateId = null): array
    {
        return $this->validator->validate($countryCode, $persistedStateId, $assignments, $stateId);
    }

    /**
     * Delete persisted assignments that are no longer valid under the
     * address's current persisted geography.
     *
     * Still-valid roles are left untouched. Validity is decided solely by
     * validate(), so pruning can never disagree with synchronization.
     */
    public function pruneIncompatibleAssignments(string $addressId): void
    {
        $addressClass = ModelResolver::addressClass();

        (new $addressClass)->getConnection()->transaction(function () use ($addressId): void {
            $persisted = AddressOwnerGuard::lockAddressForWrite($addressId);

            /** @var array<string, string> $current */
            $current = ModelResolver::areaAssignmentClass()::query()
                ->where('address_id', $addressId)
                ->pluck('address_area_id', 'role')
                ->map(static fn (mixed $areaId): string => (string) $areaId)
                ->all();

            if ($current === []) {
                return;
            }

            $removedRoles = array_map(
                static fn (int | string $role): string => (string) $role,
                array_keys(array_diff_key($current, $this->narrowToValidAssignments($persisted->country_code, $persisted->state_id, $current))),
            );

            if ($removedRoles === []) {
                return;
            }

            ModelResolver::areaAssignmentClass()::query()
                ->where('address_id', $addressId)
                ->whereIn('role', $removedRoles)
                ->delete();
        });
    }

    public function narrowToValidAssignments(?string $countryCode, ?string $persistedStateId, array $assignments): array
    {
        return $this->validator->narrowToValidAssignments($countryCode, $persistedStateId, $assignments);
    }
}
