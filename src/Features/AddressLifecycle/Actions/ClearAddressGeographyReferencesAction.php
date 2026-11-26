<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\AddressLifecycle\Actions;

use AIArmada\Addressing\Contracts\AddressAssignmentWriter;
use AIArmada\Addressing\Features\AddressLifecycle\Api\AddressReferenceMaintenance;
use AIArmada\Addressing\Features\AddressLifecycle\Support\AddressAreaAssignmentOwnerScope;
use AIArmada\Addressing\Support\ModelResolver;

final readonly class ClearAddressGeographyReferencesAction implements AddressReferenceMaintenance
{
    private const int CHUNK_SIZE = 500;

    public function __construct(private AddressAssignmentWriter $assignments) {}

    public function clearState(string $stateId): void
    {
        $addressClass = ModelResolver::addressClass();

        (new $addressClass)->getConnection()->transaction(function () use ($stateId, $addressClass): void {
            $assignmentClass = ModelResolver::areaAssignmentClass();
            $assignmentKeyName = (new $assignmentClass)->getKeyName();
            $lastId = null;

            do {
                $addresses = $addressClass::query()->withoutOwnerScope()
                    ->where('state_id', $stateId)
                    ->when($lastId !== null, static fn ($query) => $query->where('id', '>', $lastId))
                    ->orderBy('id')
                    ->limit(self::CHUNK_SIZE)
                    ->lockForUpdate()
                    ->get(['id', 'country_code']);

                if ($addresses->isEmpty()) {
                    break;
                }

                $ids = $addresses
                    ->map(static fn ($address): string => (string) $address->getKey())
                    ->all();

                $addressClass::query()->withoutOwnerScope()
                    ->whereIn('id', $ids)
                    ->where('state_id', $stateId)
                    ->update(['state_id' => null]);

                $assignments = $assignmentClass::query()
                    ->withoutGlobalScope(AddressAreaAssignmentOwnerScope::class)
                    ->whereIn('address_id', $ids)
                    ->orderBy($assignmentKeyName)
                    ->lockForUpdate()
                    ->get(['address_id', 'role', 'address_area_id']);

                $currentByAddress = [];

                foreach ($assignments as $assignment) {
                    $currentByAddress[(string) $assignment->getAttribute('address_id')][(string) $assignment->getAttribute('role')] = (string) $assignment->getAttribute('address_area_id');
                }

                $removedByRole = [];
                $validatedAssignments = [];

                foreach ($addresses as $address) {
                    $current = $currentByAddress[(string) $address->getKey()] ?? [];

                    if ($current === []) {
                        continue;
                    }

                    $countryCode = $address->getAttribute('country_code');
                    $validationKey = serialize([$countryCode, $current]);
                    $valid = $validatedAssignments[$validationKey] ??= $this->assignments->narrowToValidAssignments(
                        $countryCode,
                        null,
                        $current,
                    );

                    foreach (array_keys(array_diff_key($current, $valid)) as $role) {
                        $removedByRole[(string) $role][] = (string) $address->getKey();
                    }
                }

                foreach ($removedByRole as $role => $addressIds) {
                    $assignmentClass::query()
                        ->withoutGlobalScope(AddressAreaAssignmentOwnerScope::class)
                        ->whereIn('address_id', $addressIds)
                        ->where('role', $role)
                        ->delete();
                }

                $lastId = end($ids);
            } while (true);
        });
    }

    public function clearCity(string $cityId): void
    {
        ModelResolver::addressClass()::query()->withoutOwnerScope()
            ->where('city_id', $cityId)
            ->update(['city_id' => null]);
    }

    public function clearArea(string $areaId): void
    {
        ModelResolver::areaAssignmentClass()::query()
            ->withoutGlobalScope(AddressAreaAssignmentOwnerScope::class)
            ->where('address_area_id', $areaId)
            ->delete();
    }
}
