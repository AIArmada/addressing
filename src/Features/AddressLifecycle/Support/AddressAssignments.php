<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\AddressLifecycle\Support;

use AIArmada\Addressing\Features\AddressLifecycle\Models\Address;
use AIArmada\Addressing\Features\AddressLifecycle\Models\AddressAreaAssignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class AddressAssignments
{
    public const string POSTAL_LOCALITY = 'postal_locality';

    public const string ADMINISTRATIVE_DISTRICT = 'administrative_district';

    public const string ADMINISTRATIVE_DIVISION = 'administrative_division';

    public const string ADMINISTRATIVE_SUBDIVISION = 'administrative_subdivision';

    /**
     * @return array<string, string>
     */
    public static function forAddress(?Address $address): array
    {
        if (! $address instanceof Address) {
            return [];
        }

        if ($address->relationLoaded('areaAssignments')) {
            /** @var Collection<int, AddressAreaAssignment> $assignments */
            $assignments = $address->getRelation('areaAssignments');

            return $assignments
                ->where('is_primary', true)
                ->mapWithKeys(static fn (AddressAreaAssignment $assignment): array => [
                    (string) $assignment->getAttribute('role') => (string) $assignment->getAttribute('address_area_id'),
                ])
                ->all();
        }

        return $address->areaAssignments()
            ->where('is_primary', true)
            ->pluck('address_area_id', 'role')
            ->map(static fn (mixed $id): string => (string) $id)
            ->all();
    }

    public static function id(?Address $address, string $role): ?string
    {
        $id = self::forAddress($address)[$role] ?? null;

        return is_string($id) && $id !== '' ? $id : null;
    }

    /**
     * @param  array<string, mixed>  $assignments
     * @return array<string, string>
     */
    public static function normalize(array $assignments): array
    {
        $normalized = [];

        foreach ($assignments as $role => $areaId) {
            if (! is_string($role) || ! is_string($areaId)) {
                continue;
            }

            $role = mb_trim($role);
            $areaId = mb_trim($areaId);

            if ($role !== '' && $areaId !== '') {
                $normalized[$role] = Str::isUuid($areaId) ? Str::lower($areaId) : $areaId;
            }
        }

        return $normalized;
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array<string, mixed>  $assignments
     * @return Builder<Model>
     */
    public static function apply(Builder $query, array $assignments, string $relation = 'addresses'): Builder
    {
        foreach (self::normalize($assignments) as $role => $areaId) {
            $query->whereHas($relation . '.areaAssignments', static function (Builder $assignmentQuery) use ($role, $areaId): void {
                $assignmentQuery
                    ->where('role', $role)
                    ->where('address_area_id', $areaId)
                    ->where('is_primary', true);
            });
        }

        return $query;
    }
}
