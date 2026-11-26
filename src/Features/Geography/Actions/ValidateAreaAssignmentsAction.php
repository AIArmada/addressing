<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\Geography\Actions;

use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Features\Geography\Api\AreaAssignmentValidator;
use AIArmada\Addressing\Features\Geography\Api\AreaReferenceReader;
use AIArmada\Addressing\Features\Geography\Models\AddressArea;
use AIArmada\Addressing\Features\Geography\Support\AddressAreaStateBridge;
use AIArmada\Addressing\Support\CountryAddressProfileResolver;
use AIArmada\Addressing\Support\ModelResolver;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

final readonly class ValidateAreaAssignmentsAction implements AreaAssignmentValidator
{
    public function __construct(
        private CountryAddressProfileResolver $profiles,
        private AreaReferenceReader $references,
    ) {}

    /**
     * Validate an assignment map against authoritative address geography.
     *
     * Mutation callers resolve these geography values from the locked address.
     *
     * @param  array<string, string|null>  $assignments
     * @return array<string, string>
     */
    public function validate(?string $countryCode, ?string $persistedStateId, array $assignments, ?string $stateId = null): array
    {
        if ($stateId !== null) {
            if ($persistedStateId === null || (string) $stateId !== (string) $persistedStateId) {
                throw ValidationException::withMessages([
                    'state_id' => 'The selected state does not match the persisted address state.',
                ]);
            }
        }

        $countryCode = mb_strtoupper(mb_trim((string) $countryCode));
        $selectedAssignments = [];

        foreach ($assignments as $role => $areaId) {
            if (! is_string($role) || ($areaId !== null && ! is_string($areaId))) {
                throw ValidationException::withMessages(['address_areas' => 'Invalid address area assignment.']);
            }

            if ($areaId !== null && mb_trim($areaId) !== '') {
                $selectedAssignments[$role] = mb_trim($areaId);
            }
        }
        if ($selectedAssignments === []) {
            return [];
        }

        $areaIds = array_values(array_unique($selectedAssignments));
        $areas = ModelResolver::areaClass()::query()
            ->whereIn('id', $areaIds)
            ->where('country_code', $countryCode)
            ->where('is_active', true)
            ->get()
            ->keyBy(fn (AddressArea $area): string => (string) $area->getKey());

        if ($areas->count() !== count($areaIds)) {
            throw ValidationException::withMessages([
                'address_areas' => 'Every selected address area must belong to the address country.',
            ]);
        }

        foreach ($selectedAssignments as $role => $areaId) {
            $area = $areas->get($areaId);
            $definition = is_string($role) ? $this->profiles->definitionForRole($countryCode, $role) : null;

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

        $this->validateHierarchy($selectedAssignments, $stateId ?? $persistedStateId, $areas, $countryCode);

        /** @var array<string, string> $selectedAssignments */
        return $selectedAssignments;
    }

    /**
     * Reduce an assignment map to the subset that validates, dropping only
     * roles that validate() reports as offending.
     *
     * Owner-agnostic: reads only geography (`country_code`, `state_id`) and
     * global area tables, so system cleanup can reuse it cross-owner. After
     * a bulk `state_id` nulling, pass persisted geography and assignments.
     *
     * @param  array<string, string>  $assignments
     * @return array<string, string>
     */
    public function narrowToValidAssignments(?string $countryCode, ?string $persistedStateId, array $assignments): array
    {
        // Numeric-looking role keys are cast to integers by PHP. Remove
        // malformed stored roles individually, not the valid selections.
        $candidates = array_filter(
            $assignments,
            static fn (mixed $areaId, mixed $role): bool => is_string($role)
                && $role !== ''
                && is_string($areaId)
                && mb_trim($areaId) !== '',
            ARRAY_FILTER_USE_BOTH,
        );

        while ($candidates !== []) {
            try {
                return $this->validate($countryCode, $persistedStateId, $candidates);
            } catch (ValidationException $exception) {
                $offending = array_keys($exception->errors());

                if (in_array('address_areas', $offending, true)) {
                    $narrowed = $this->retainUsableAreas($countryCode, $candidates);

                    if ($narrowed === $candidates) {
                        return [];
                    }

                    $candidates = $narrowed;

                    continue;
                }

                $remaining = array_diff_key($candidates, array_fill_keys($offending, true));

                if ($remaining === $candidates) {
                    return [];
                }

                $candidates = $remaining;
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
    private function retainUsableAreas(?string $countryCode, array $assignments): array
    {
        $countryCode = mb_strtoupper(mb_trim((string) $countryCode));

        $usable = ModelResolver::areaClass()::query()
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
        array $selectedAssignments,
        ?string $stateId,
        Collection $areas,
        string $countryCode,
    ): void {
        /** @var array<string, array{hierarchy: AddressHierarchyDefinition, level: AddressLevelDefinition}> $definitions */
        $definitions = [];

        foreach (array_keys($selectedAssignments) as $role) {
            $definition = $this->profiles->definitionForRole($countryCode, $role);

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
                $resolvedStateId = $stateId;
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
                $resolvedStateId = $stateId;
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
     * @param  Collection<string, Collection<int, array{parent_id: string, child_id: string, hierarchy_type: ?string}>>  $parents
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
                if ($hierarchyType !== null && $relationship['hierarchy_type'] !== $hierarchyType) {
                    continue;
                }

                $parentId = $relationship['parent_id'];

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
     * @return Collection<string, Collection<int, array{parent_id: string, child_id: string, hierarchy_type: ?string}>>
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

            $relationships = $this->references->activeContainmentParents($frontier);

            foreach ($relationships as $relationship) {
                $parents->put(
                    $relationship['child_id'],
                    $parents->get($relationship['child_id'], collect())->push($relationship),
                );
                $pending[] = $relationship['parent_id'];
            }
        }

        return $parents;
    }
}
