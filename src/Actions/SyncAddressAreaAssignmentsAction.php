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
            $definition = is_string($role) ? $this->definitionForRole($address, $role) : null;

            if (! $area instanceof AddressArea || $definition === null) {
                throw ValidationException::withMessages([
                    is_string($role) ? $role : 'address_areas' => 'The selected address area role is not defined by the country address profile.',
                ]);
            }

            if (! $this->areaMatchesDefinition($area, $definition['level'])) {
                throw ValidationException::withMessages([
                    $role => 'The selected area does not match the required hierarchy level.',
                ]);
            }
        }

        $this->validateHierarchy($address, $selectedAssignments, $stateId, $areas, $countryCode);

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

    /** @return array{hierarchy: AddressHierarchyDefinition, level: AddressLevelDefinition}|null */
    private function definitionForRole(Address $address, string $role): ?array
    {
        foreach ($this->profiles->hierarchies($address->country_code) as $hierarchy) {
            foreach ($hierarchy->levels as $level) {
                $assignmentRole = $level->assignmentRole ?? "{$hierarchy->key}_{$level->key}";

                if ($level->kind !== 'state' && $assignmentRole === $role) {
                    return ['hierarchy' => $hierarchy, 'level' => $level];
                }
            }
        }

        return null;
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
            $definition = $this->definitionForRole($address, $role);

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

            $parentRole = $this->roleForLevel($definition['hierarchy'], $parentDefinition);
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

    private function roleForLevel(AddressHierarchyDefinition $hierarchy, AddressLevelDefinition $level): string
    {
        return $level->assignmentRole ?? "{$hierarchy->key}_{$level->key}";
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
