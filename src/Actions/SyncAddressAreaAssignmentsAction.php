<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Models\Address;
use AIArmada\Addressing\Models\AddressArea;
use AIArmada\Addressing\Models\AddressAreaAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SyncAddressAreaAssignmentsAction
{
    /**
     * @param  array<string, string|null>  $assignments
     */
    public function execute(Address $address, array $assignments): void
    {
        if ($assignments === []) {
            return;
        }

        $selectedAssignments = array_filter(
            $assignments,
            static fn (mixed $areaId): bool => is_string($areaId) && mb_trim($areaId) !== '',
        );

        $areaIds = array_values($selectedAssignments);
        $areas = AddressArea::query()
            ->whereIn('id', $areaIds)
            ->where('country_code', mb_strtoupper((string) $address->country_code))
            ->with('roles')
            ->get()
            ->keyBy(fn (AddressArea $area): string => (string) $area->getKey());

        if ($areas->count() !== count(array_unique($areaIds))) {
            throw ValidationException::withMessages([
                'address_areas' => 'Every selected address area must belong to the address country.',
            ]);
        }

        foreach ($selectedAssignments as $role => $areaId) {
            $area = $areas->get($areaId);

            if (! $area instanceof AddressArea) {
                continue;
            }

            $requiredRole = str_starts_with($role, 'postal_') ? 'locality' : 'administrative_area';

            $validByRole = $area->roles->contains('role', $requiredRole);
            $validByType = str_starts_with($role, 'postal_')
                ? in_array($area->type, ['locality', 'precinct'], true)
                : in_array($area->type, ['state', 'wilayah_persekutuan', 'district', 'city', 'municipality', 'mukim', 'subdistrict'], true);

            if (! $validByRole && ! $validByType) {
                throw ValidationException::withMessages([
                    $role => "The selected area is not a valid {$requiredRole}.",
                ]);
            }
        }

        DB::transaction(function () use ($address, $assignments, $selectedAssignments): void {
            AddressAreaAssignment::query()
                ->where('address_id', $address->getKey())
                ->whereIn('role', array_keys($assignments))
                ->delete();

            foreach ($selectedAssignments as $role => $areaId) {
                AddressAreaAssignment::query()->create([
                    'address_id' => $address->getKey(),
                    'address_area_id' => $areaId,
                    'role' => $role,
                    'is_primary' => true,
                    'metadata' => ['source' => 'filament-addressing'],
                ]);
            }
        });
    }
}
