<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\Address;
use AIArmada\Addressing\Models\AddressArea;
use AIArmada\Addressing\Models\AddressAreaAssignment;
use AIArmada\Addressing\Models\AddressAreaRelationship;
use AIArmada\Addressing\Support\AddressAreaStateBridge;
use AIArmada\Addressing\Support\AddressOwnerGuard;
use AIArmada\Addressing\Support\CountryAddressProfileResolver;
use AIArmada\Addressing\Support\ModelResolver;
use AIArmada\CommerceSupport\Support\OwnerScope;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SyncAddressAreaAssignmentsAction
{
    public function __construct(
        private readonly CountryAddressProfileResolver $profiles,
    ) {}

    /**
     * @param  array<string, string|null>  $assignments
     * @param  array<string, mixed>  $metadata
     */
    public function execute(
        Address $address,
        array $assignments,
        ?string $stateId = null,
        array $metadata = [],
    ): void {
        AddressOwnerGuard::assertAddressIsWritable($address->getKey());

        if ($assignments === []) {
            AddressAreaAssignment::query()
                ->where('address_id', $address->getKey())
                ->delete();

            return;
        }

        $selectedAssignments = $this->validate($this->persistedAddress($address), $assignments, $stateId);

        DB::transaction(function () use ($address, $selectedAssignments, $metadata): void {
            AddressAreaAssignment::query()
                ->where('address_id', $address->getKey())
                ->delete();

            foreach ($selectedAssignments as $role => $areaId) {
                AddressAreaAssignment::query()->create([
                    'address_id' => $address->getKey(),
                    'address_area_id' => $areaId,
                    'role' => $role,
                    'is_primary' => true,
                    'metadata' => $metadata !== [] ? $metadata : null,
                ]);
            }
        });
    }

    /**
     * Validate an assignment map against authoritative address geography.
     *
     * Callers must pass persisted address data: the caller's in-memory model
     * may carry unsaved or stale country/state values that must never widen
     * or narrow validation.
     *
     * @param  array<string, string|null>  $assignments
     * @return array<string, string>
     */
    public function validate(Address $address, array $assignments, ?string $stateId = null): array
    {
        if ($stateId !== null) {
            $persistedStateId = $address->state_id;

            if ($persistedStateId === null || (string) $stateId !== (string) $persistedStateId) {
                throw ValidationException::withMessages([
                    'state_id' => 'The selected state does not match the persisted address state.',
                ]);
            }
        }

        $countryCode = mb_strtoupper(mb_trim((string) $address->country_code));
        $selectedAssignments = array_filter(
            $assignments,
            static fn (mixed $areaId): bool => is_string($areaId) && mb_trim($areaId) !== '',
        );
        $areaIds = array_values($selectedAssignments);
        $areas = AddressArea::query()
            ->whereIn('id', $areaIds)
            ->where('country_code', $countryCode)
            ->where('is_active', true)
            ->get()
            ->keyBy(fn (AddressArea $area): string => (string) $area->getKey());

        if ($areas->count() !== count(array_unique($areaIds))) {
            throw ValidationException::withMessages([
                'address_areas' => 'Every selected address area must belong to the address country.',
            ]);
        }

        foreach ($selectedAssignments as $role => $areaId) {
            $area = $areas->get($areaId);
            $definition = is_string($role) ? $this->profiles->definitionForRole($address->country_code, $role) : null;

            if (! $area instanceof AddressArea || $definition === null) {
                throw ValidationException::withMessages([
                    $role => 'The selected address area role is not defined by the country address profile.',
                ]);
            }

            if ($definition['level']->kind !== 'area') {
                throw ValidationException::withMessages([
                    $role => 'The selected role is not an assignable area role.',
                ]);
            }

            if (! $this->areaMatchesDefinition($area, $definition['level'])) {
                throw ValidationException::withMessages([
                    $role => 'The selected area does not match the required hierarchy level.',
                ]);
            }
        }

        $this->validateHierarchy($address, $selectedAssignments, $stateId, $areas, $countryCode);

        /** @var array<string, string> $selectedAssignments */
        return $selectedAssignments;
    }

    /**
     * Delete persisted assignments that are no longer valid under the
     * address's current persisted geography.
     *
     * Still-valid roles are left untouched. Validity is decided solely by
     * validate(), so pruning can never disagree with synchronization.
     */
    public function pruneIncompatibleAssignments(Address $address): void
    {
        AddressOwnerGuard::assertAddressIsWritable($address->getKey());

        $persisted = $this->persistedAddress($address);

        /** @var array<string, string> $current */
        $current = AddressAreaAssignment::query()
            ->where('address_id', $address->getKey())
            ->pluck('address_area_id', 'role')
            ->map(static fn (mixed $areaId): string => (string) $areaId)
            ->all();

        if ($current === []) {
            return;
        }

        $removedRoles = array_map(
            static fn (int | string $role): string => (string) $role,
            array_keys(array_diff_key($current, $this->narrowToValidAssignments($persisted, $current))),
        );

        if ($removedRoles === []) {
            return;
        }

        AddressAreaAssignment::query()
            ->where('address_id', $address->getKey())
            ->whereIn('role', $removedRoles)
            ->delete();
    }

    /**
     * Reduce an assignment map to the subset that validates, dropping only
     * roles that validate() reports as offending.
     *
     * Owner-agnostic: reads only geography (`country_code`, `state_id`) and
     * global area tables, so system cleanup can reuse it cross-owner. After
     * a bulk `state_id` nulling that bypasses model events (for example
     * state reference cleanup), reload the persisted address, pass its
     * current assignments, and delete the returned diff without owner scope.
     *
     * @param  array<string, string>  $assignments
     * @return array<string, string>
     */
    public function narrowToValidAssignments(Address $address, array $assignments): array
    {
        $candidates = $assignments;

        while ($candidates !== []) {
            try {
                return $this->validate($address, $candidates);
            } catch (ValidationException $exception) {
                $offending = array_keys($exception->errors());

                if (in_array('address_areas', $offending, true)) {
                    $narrowed = $this->retainUsableAreas($address, $candidates);

                    if ($narrowed === $candidates) {
                        return [];
                    }

                    $candidates = $narrowed;

                    continue;
                }

                foreach ($offending as $role) {
                    unset($candidates[$role]);
                }
            }
        }

        return [];
    }

    /**
     * Keep only candidate areas that exist, are active, and belong to the
     * address country: the same membership rule validate() enforces, used
     * here to isolate which roles a country-level failure implicates.
     *
     * @param  array<string, string>  $assignments
     * @return array<string, string>
     */
    private function retainUsableAreas(Address $address, array $assignments): array
    {
        $countryCode = mb_strtoupper(mb_trim((string) $address->country_code));

        $usable = AddressArea::query()
            ->whereIn('id', array_values($assignments))
            ->where('country_code', $countryCode)
            ->where('is_active', true)
            ->pluck('id')
            ->map(static fn (mixed $id): string => (string) $id)
            ->flip()
            ->all();

        return array_filter(
            $assignments,
            static fn (mixed $areaId): bool => is_string($areaId) && isset($usable[$areaId]),
        );
    }

    /**
     * Reload the authoritative persisted row for validation.
     *
     * The caller's model may carry unsaved mutations or stale values; those
     * must never widen or narrow validation, and this reload must not persist
     * them back.
     */
    private function persistedAddress(Address $address): Address
    {
        $addressClass = ModelResolver::addressClass();
        $query = $addressClass::query();

        if (! $addressClass::ownerScopeConfig()->enabled) {
            $query->withoutGlobalScope(OwnerScope::class);
        }

        return $query->whereKey($address->getKey())->firstOrFail();
    }

    private function areaMatchesDefinition(AddressArea $area, AddressLevelDefinition $definition): bool
    {
        $types = $definition->areaTypes !== []
            ? $definition->areaTypes
            : ($definition->areaType !== null ? [$definition->areaType] : []);
        $levels = $definition->areaLevels !== []
            ? $definition->areaLevels
            : ($definition->areaLevel !== null ? [$definition->areaLevel] : []);

        return ($types === [] || in_array($area->type, $types, true))
            && ($levels === [] || in_array($area->level, $levels, true));
    }

    /**
     * @param  array<string, string>  $selectedAssignments
     * @param  Collection<string, AddressArea>  $areas
     */
    private function validateHierarchy(
        Address $address,
        array $selectedAssignments,
        ?string $stateId,
        Collection $areas,
        string $countryCode,
    ): void {
        /** @var array<string, array{hierarchy: AddressHierarchyDefinition, level: AddressLevelDefinition}> $definitions */
        $definitions = [];

        foreach (array_keys($selectedAssignments) as $role) {
            $definition = $this->profiles->definitionForRole($address->country_code, $role);

            if ($definition !== null) {
                $definitions[$role] = $definition;
            }
        }

        $parents = $this->loadAncestorRelationships($areas->keys()->all());
        $ancestorIds = [];

        foreach (array_unique(array_filter(array_map(
            fn (array $definition): string => $this->hierarchyType($definition),
            $definitions,
        ))) as $hierarchyType) {
            foreach ($areas as $area) {
                $ancestorIds[$hierarchyType][(string) $area->getKey()] = $this->ancestorIds(
                    (string) $area->getKey(),
                    $parents,
                    $hierarchyType,
                );
            }
        }

        foreach ($definitions as $role => $definition) {
            $level = $definition['level'];

            if ($level->parentKey === null) {
                continue;
            }

            $parentDefinition = collect($definition['hierarchy']->levels)
                ->first(fn (AddressLevelDefinition $candidate): bool => $candidate->key === $level->parentKey);
            $areaId = $selectedAssignments[$role];

            if (! $parentDefinition instanceof AddressLevelDefinition) {
                throw ValidationException::withMessages([
                    $role => 'The selected area requires a parent level defined by the country address profile.',
                ]);
            }

            if ($parentDefinition->kind === 'state') {
                $resolvedStateId = $stateId ?? $address->state_id;
                $stateAreaId = AddressAreaStateBridge::areaIdForState($resolvedStateId, $this->hierarchyType($definition));

                if ($stateAreaId === null || ! in_array($stateAreaId, $ancestorIds[$this->hierarchyType($definition)][$areaId] ?? [], true)) {
                    throw ValidationException::withMessages([
                        $role => sprintf('The selected area must belong to the selected %s.', $parentDefinition->label),
                    ]);
                }

                continue;
            }

            $parentRole = CountryAddressProfileResolver::roleForLevel($definition['hierarchy'], $parentDefinition);
            $parentAreaId = $selectedAssignments[$parentRole] ?? null;

            if (! is_string($parentAreaId)) {
                $resolvedStateId = $stateId ?? $address->state_id;
                $stateAreaId = $resolvedStateId !== null
                    ? AddressAreaStateBridge::areaIdForState($resolvedStateId, $this->hierarchyType($definition))
                    : null;

                if ($stateAreaId !== null && in_array($stateAreaId, $ancestorIds[$this->hierarchyType($definition)][$areaId] ?? [], true)) {
                    continue;
                }

                throw ValidationException::withMessages([
                    $role => 'The selected area requires its parent hierarchy level to be selected first.',
                ]);
            }

            if (! in_array($parentAreaId, $ancestorIds[$this->hierarchyType($definition)][$areaId] ?? [], true)) {
                throw ValidationException::withMessages([
                    $role => 'The selected area must be inside the selected parent area.',
                ]);
            }
        }
    }

    /** @param array{hierarchy: AddressHierarchyDefinition, level: AddressLevelDefinition} $definition */
    private function hierarchyType(array $definition): string
    {
        return $definition['level']->hierarchyType ?? $definition['hierarchy']->key;
    }

    /**
     * @param  Collection<string, Collection<int, AddressAreaRelationship>>  $parents
     * @return list<string>
     */
    private function ancestorIds(string $areaId, Collection $parents, ?string $hierarchyType): array
    {
        $ancestors = [];
        $pending = [$areaId];

        while ($pending !== []) {
            $currentId = array_shift($pending);

            if (! is_string($currentId) || isset($ancestors[$currentId])) {
                continue;
            }

            $ancestors[$currentId] = true;

            foreach ($parents->get($currentId, []) as $relationship) {
                if ($hierarchyType !== null && $relationship->hierarchy_type !== $hierarchyType) {
                    continue;
                }

                $parentId = (string) $relationship->parent_address_area_id;

                if (! isset($ancestors[$parentId])) {
                    $pending[] = $parentId;
                }
            }
        }

        unset($ancestors[$areaId]);

        return array_keys($ancestors);
    }

    /**
     * Load only the ancestor graph reachable from the selected areas.
     *
     * The previous implementation loaded every active relationship for the
     * address country. A breadth-first frontier keeps the query count bounded
     * by hierarchy depth while avoiding unrelated country data.
     *
     * @param  list<string>  $areaIds
     * @return Collection<string, Collection<int, AddressAreaRelationship>>
     */
    private function loadAncestorRelationships(array $areaIds): Collection
    {
        $parents = collect();
        $pending = array_values(array_unique($areaIds));
        $visited = [];

        while ($pending !== []) {
            $frontier = array_values(array_filter(
                array_unique($pending),
                static fn (mixed $areaId): bool => is_string($areaId) && ! isset($visited[$areaId]),
            ));
            $pending = [];

            if ($frontier === []) {
                break;
            }

            foreach ($frontier as $areaId) {
                $visited[$areaId] = true;
            }

            $relationships = AddressAreaRelationship::query()
                ->whereIn('child_address_area_id', $frontier)
                ->where('relationship_type', 'contains')
                ->where(function ($query): void {
                    $query->whereNull('valid_from')->orWhereDate('valid_from', '<=', CarbonImmutable::now());
                })
                ->where(function ($query): void {
                    $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', CarbonImmutable::now());
                })
                ->get(['parent_address_area_id', 'child_address_area_id', 'hierarchy_type']);

            foreach ($relationships as $relationship) {
                $parents->put(
                    (string) $relationship->child_address_area_id,
                    $parents->get((string) $relationship->child_address_area_id, collect())->push($relationship),
                );
                $pending[] = (string) $relationship->parent_address_area_id;
            }
        }

        return $parents;
    }
}
