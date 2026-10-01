<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Actions\SyncAddressAreaAssignmentsAction;
use AIArmada\Addressing\Models\AddressAreaAssignment;
use AIArmada\Addressing\Models\AddressAreaStateLink;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Models\City;
use AIArmada\Addressing\Models\State;
use Illuminate\Support\Facades\DB;

/**
 * Application-level reference integrity for shared geography rows.
 *
 * States and cities are global reference data while addresses are
 * tenant-owned. Deleting a state or city nulls the live foreign pointers
 * (keeping reusable free-text identity) and clears area-state links,
 * instead of relying on database cascades. Address maintenance is
 * explicitly cross-owner: every tenant's dangling pointer must clear
 * without depending on the current owner context.
 *
 * Nulling `state_id` via a bulk query bypasses the Address `updated` hook,
 * so state cleanup prunes incompatible area assignments directly through
 * the owner-agnostic narrowToValidAssignments() seam. Assignments that
 * remain valid under a null state are kept; only roles that fail
 * revalidation are removed. Snapshots stay frozen: cleanup never rewrites
 * them.
 */
final class GeographyReferenceCleanup
{
    private const int CHUNK_SIZE = 500;

    /**
     * Delete stale states for a country by code through model deletes so
     * reference cleanup runs for every row. Returns the deleted count.
     *
     * @param  list<string>  $codes
     */
    public static function pruneStatesByCodes(AddressCountry $country, array $codes): int
    {
        $codes = array_values(array_unique(array_filter(array_map(
            static fn (mixed $code): string => mb_trim((string) $code),
            $codes,
        ), static fn (string $code): bool => $code !== '')));

        if ($codes === []) {
            return 0;
        }

        $stateClass = ModelResolver::stateClass();
        $deleted = 0;

        $stateClass::query()
            ->where('country_id', $country->getKey())
            ->whereIn('code', $codes)
            ->lazyById(self::CHUNK_SIZE)
            ->each(static function (State $state) use (&$deleted): void {
                DB::transaction(static function () use ($state, &$deleted): void {
                    $state->delete();
                    $deleted++;
                });
            });

        return $deleted;
    }

    public static function cleanupStateReferences(State $state): void
    {
        $stateId = (string) $state->getKey();
        $addressClass = ModelResolver::addressClass();
        $cityClass = ModelResolver::cityClass();

        DB::transaction(static function () use ($stateId, $addressClass, $cityClass): void {
            $cityClass::query()
                ->where('state_id', $stateId)
                ->update(['state_id' => null]);

            AddressAreaStateLink::query()
                ->where('state_id', $stateId)
                ->delete();

            AddressAreaStateBridge::forgetState($stateId);

            // Explicit cross-owner maintenance: withoutOwnerScope() reaches
            // every tenant's rows without depending on the caller context.
            // Chunked by id to avoid loading all affected addresses at once.
            $lastId = null;

            do {
                $ids = $addressClass::query()->withoutOwnerScope()
                    ->where('state_id', $stateId)
                    ->when($lastId !== null, static fn ($query) => $query->where('id', '>', $lastId))
                    ->orderBy('id')
                    ->limit(self::CHUNK_SIZE)
                    ->pluck('id')
                    ->map(static fn (mixed $id): string => (string) $id)
                    ->all();

                if ($ids === []) {
                    break;
                }

                $addressClass::query()->withoutOwnerScope()
                    ->whereIn('id', $ids)
                    ->update(['state_id' => null]);

                foreach ($ids as $id) {
                    self::pruneAddressAssignments($addressClass, $id);
                }

                $lastId = end($ids);
            } while (true);
        });
    }

    public static function cleanupCityReferences(City $city): void
    {
        $cityId = (string) $city->getKey();

        // City pointers carry no assignment hook (Address only prunes on
        // country/state changes), so a cross-owner null is sufficient.
        ModelResolver::addressClass()::query()->withoutOwnerScope()
            ->where('city_id', $cityId)
            ->update(['city_id' => null]);
    }

    /**
     * Revalidate one address after its state pointer was nulled.
     *
     * Geography-only: narrowToValidAssignments() reads just the persisted
     * country/state and global area tables, so no owner resolution is
     * needed. Assignment reads and deletes opt out of the assignment owner
     * scope explicitly, which also keeps a runtime owner-disabled flip from
     * tripping over a scope booted while owner mode was enabled.
     */
    private static function pruneAddressAssignments(string $addressClass, string $addressId): void
    {
        $address = $addressClass::query()->withoutOwnerScope()->whereKey($addressId)->first();

        if ($address === null) {
            return;
        }

        $current = AddressAreaAssignment::query()
            ->withoutGlobalScope(AddressAreaAssignmentOwnerScope::class)
            ->where('address_id', $addressId)
            ->pluck('address_area_id', 'role')
            ->map(static fn (mixed $areaId): string => (string) $areaId)
            ->all();

        if ($current === []) {
            return;
        }

        $removedRoles = array_map(
            static fn (int | string $role): string => (string) $role,
            array_keys(array_diff_key($current, app(SyncAddressAreaAssignmentsAction::class)->narrowToValidAssignments($address, $current))),
        );

        if ($removedRoles === []) {
            return;
        }

        AddressAreaAssignment::query()
            ->withoutGlobalScope(AddressAreaAssignmentOwnerScope::class)
            ->where('address_id', $addressId)
            ->whereIn('role', $removedRoles)
            ->delete();
    }
}
