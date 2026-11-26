<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\Geography\Support;

use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Data\GooglePlaceAddressComponents;
use AIArmada\Addressing\Features\Geography\Api\PlaceGeographyResolver;
use AIArmada\Addressing\Features\Geography\Models\AddressArea;
use AIArmada\Addressing\Features\Geography\Models\AddressCountry;
use AIArmada\Addressing\Features\PostalCodes\Api\PostalAreaLookup;
use AIArmada\Addressing\Support\CountryAddressProfileResolver;
use AIArmada\Addressing\Support\ModelResolver;

final readonly class GooglePlaceGeographyResolver implements PlaceGeographyResolver
{
    public function __construct(
        private AddressAreaHierarchyResolver $addressAreaHierarchyResolver,
        private CountryAddressProfileResolver $profiles,
        private GooglePlaceGeographyMatcher $matcher,
        private GooglePlaceResolutionGapLogger $gapLogger,
        private PostalAreaLookup $postalAreas,
    ) {}

    /**
     * @return array{
     *     country_id: string|null,
     *     state_id: string|null,
     *     city_id: string|null,
     *     area_assignments: array<string, string>,
     *     state: string|null,
     *     city: string|null,
     *     inferred_area_roles: list<string>
     * }
     */
    public function resolve(
        GooglePlaceAddressComponents $components,
        ?string $placeName,
        ?string $fallbackCountryId,
        ?string $placeId,
    ): array {
        $country = $this->matcher->resolveCountry($components);
        $countryId = ($country instanceof AddressCountry ? (string) $country->getKey() : null)
            ?? $fallbackCountryId;
        $countryCode = $this->matcher->resolveCountryCode($country, $countryId);

        $stateName = $components->value(['administrative_area_level_1']);
        $cityName = $components->value(['locality']) ?? $components->value(['postal_town']);
        $postcode = $components->value(['postal_code']);

        $profiles = $this->profiles;
        $hierarchies = $countryId !== null ? $profiles->hierarchies($countryId) : [];
        $stateLevel = $countryId !== null ? $profiles->stateLevel($countryId) : null;
        $stateTypes = $this->stateAreaTypes($stateLevel);
        $areaTreeRoot = $stateTypes !== []
            ? $this->matcher->resolveArea($stateName, $countryId, null, $stateTypes, null, $countryCode)
            : null;
        $stateId = $this->matcher->resolveStateId($stateName, $countryId, $areaTreeRoot, $countryCode);
        $areaTreeRootId = AddressAreaStateBridge::areaIdForState($stateId, 'administrative');
        $areaTreeRoot = $areaTreeRootId !== null
            ? ModelResolver::areaClass()::query()->find($areaTreeRootId)
            : $areaTreeRoot;

        if (! $areaTreeRoot instanceof AddressArea) {
            $areaTreeRoot = null;
        }

        /** @var array<string, array{hierarchy: AddressHierarchyDefinition, level: AddressLevelDefinition, area: AddressArea}> $resolved */
        $resolved = [];
        /** @var array<string, list<string>> $resolvedRolesByHierarchy */
        $resolvedRolesByHierarchy = [];
        /** @var array<string, list<string>> $misses */
        $misses = [];
        /** @var list<string> $inferredAreaRoles */
        $inferredAreaRoles = [];

        $this->resolveStructuralLevels($hierarchies, $components, $placeName, $countryId, $countryCode, $stateId, $profiles, $areaTreeRoot, $resolved, $resolvedRolesByHierarchy, $misses, $inferredAreaRoles);
        $this->resolvePostalLevels($hierarchies, $components, $postcode, $placeName, $countryId, $countryCode, $stateId, $profiles, $resolved, $resolvedRolesByHierarchy, $misses, $inferredAreaRoles, $areaTreeRoot);
        $this->recoverAncestors($hierarchies, $resolved, $resolvedRolesByHierarchy);

        if (! $areaTreeRoot instanceof AddressArea) {
            $areaTreeRoot = $this->recoverAreaTreeRoot($hierarchies, $stateLevel, $stateTypes, $resolved);
        }

        $countryCandidates = [$areaTreeRoot?->country_id];

        foreach ($this->resolvedEntriesInOrder($hierarchies, $resolved) as $entry) {
            $countryCandidates[] = $entry['area']->country_id;
        }

        $countryCandidates[] = $countryId;
        $countryId = $this->preferredString($countryCandidates);

        $stateId ??= $this->matcher->resolveStateId($stateName, $countryId, $areaTreeRoot, $countryCode);
        $stateId ??= $this->recoverStateId($hierarchies, $resolved);
        $cityId = $this->matcher->resolveCityId($cityName, $stateId, $countryId, $countryCode);

        $this->gapLogger->log(
            $misses,
            $resolved,
            $stateName,
            $stateId,
            $countryId !== null && $profiles->stateLevel($countryId) instanceof AddressLevelDefinition,
            $countryCode,
            $placeId,
        );

        $assignments = [];

        foreach ($resolved as $role => $entry) {
            $assignments[$role] = (string) $entry['area']->getKey();
        }

        return [
            'country_id' => $countryId,
            'state_id' => $stateId,
            'city_id' => $cityId,
            'area_assignments' => $this->orderAssignments($hierarchies, $assignments),
            'state' => $stateId === null ? $stateName : null,
            'city' => $cityId === null ? $cityName : null,
            'inferred_area_roles' => $inferredAreaRoles,
        ];
    }

    /**
     * @param  list<AddressHierarchyDefinition>  $hierarchies
     * @param  array<string, array{hierarchy: AddressHierarchyDefinition, level: AddressLevelDefinition, area: AddressArea}>  $resolved
     * @param  array<string, list<string>>  $resolvedRolesByHierarchy
     * @param  array<string, list<string>>  $misses
     * @param  list<string>  $inferredAreaRoles
     */
    private function resolveStructuralLevels(
        array $hierarchies,
        GooglePlaceAddressComponents $components,
        ?string $placeName,
        ?string $countryId,
        ?string $countryCode,
        ?string $stateId,
        CountryAddressProfileResolver $profiles,
        ?AddressArea $areaTreeRoot,
        array &$resolved,
        array &$resolvedRolesByHierarchy,
        array &$misses,
        array &$inferredAreaRoles,
    ): void {
        foreach ($hierarchies as $hierarchy) {
            if ($hierarchy->key === 'postal') {
                continue;
            }

            $areaDepth = -1;

            foreach ($hierarchy->levels as $level) {
                if ($level->kind !== 'area') {
                    continue;
                }

                $areaDepth++;
                $role = CountryAddressProfileResolver::roleForLevel($hierarchy, $level);
                $types = $this->levelTypes($level);
                $levels = $this->levelLevels($level);

                if ($types === []) {
                    continue;
                }

                $componentTypes = GooglePlaceComponentMapper::componentTypesForLevel($hierarchy, $areaDepth);
                $names = $components->candidateNames($componentTypes);
                $areaIdsByRole = [];

                foreach ($resolved as $resolvedRole => $entry) {
                    $areaIdsByRole[$resolvedRole] = (string) $entry['area']->getKey();
                }

                $parentId = $profiles->parentAreaIdForRole(
                    $countryId,
                    $role,
                    $stateId,
                    $areaIdsByRole,
                    fn (string $parentRole, string $candidateParentId): bool => $this->matcher->scopedAreaQuery(
                        $countryId,
                        $candidateParentId,
                        $types,
                        $levels !== [] ? $levels : null,
                    )->exists(),
                )
                    ?? $this->deepestResolvedId($hierarchy->key, $resolved, $resolvedRolesByHierarchy)
                    ?? ($areaTreeRoot instanceof AddressArea ? (string) $areaTreeRoot->getKey() : null);

                $area = $names !== []
                    ? $this->matchLevel(
                        $names,
                        $countryId,
                        $countryCode,
                        $parentId,
                        $areaTreeRoot,
                        $hierarchy,
                        $level,
                    )
                    : null;

                $primaryComponentType = $componentTypes[0] ?? null;
                $hasExplicitPrimaryValue = is_string($primaryComponentType)
                    && $components->value([$primaryComponentType]) !== null;

                if (
                    ! $hasExplicitPrimaryValue
                    && ! $area instanceof AddressArea
                    && $parentId !== null
                ) {
                    $inferredArea = $this->matcher->resolveAreaFromPlaceName(
                        $placeName,
                        $countryId,
                        $parentId,
                        $countryCode,
                        $types,
                        $levels,
                    );

                    if ($inferredArea instanceof AddressArea) {
                        $area = $inferredArea;
                        $inferredAreaRoles[] = $role;
                        unset($misses[$role]);
                    }
                }

                if ($area instanceof AddressArea) {
                    $resolved[$role] = ['hierarchy' => $hierarchy, 'level' => $level, 'area' => $area];

                    if (! in_array($role, $resolvedRolesByHierarchy[$hierarchy->key] ?? [], true)) {
                        $resolvedRolesByHierarchy[$hierarchy->key][] = $role;
                    }
                } elseif ($names !== []) {
                    $misses[$role] ??= $names;
                }
            }
        }
    }

    /**
     * @param  list<AddressHierarchyDefinition>  $hierarchies
     * @param  array<string, array{hierarchy: AddressHierarchyDefinition, level: AddressLevelDefinition, area: AddressArea}>  $resolved
     * @param  array<string, list<string>>  $resolvedRolesByHierarchy
     * @param  array<string, list<string>>  $misses
     * @param  list<string>  $inferredAreaRoles
     */
    private function resolvePostalLevels(
        array $hierarchies,
        GooglePlaceAddressComponents $components,
        ?string $postcode,
        ?string $placeName,
        ?string $countryId,
        ?string $countryCode,
        ?string $stateId,
        CountryAddressProfileResolver $profiles,
        array &$resolved,
        array &$resolvedRolesByHierarchy,
        array &$misses,
        array &$inferredAreaRoles,
        ?AddressArea $areaTreeRoot,
    ): void {
        $structuralResolved = false;

        foreach ($resolved as $entry) {
            if ($entry['hierarchy']->key !== 'postal') {
                $structuralResolved = true;

                break;
            }
        }

        foreach ($hierarchies as $hierarchy) {
            if ($hierarchy->key !== 'postal') {
                continue;
            }

            $this->resolvePostalFromPostcode($postcode, $countryId, $countryCode, $hierarchy, $resolved, $resolvedRolesByHierarchy);

            $areaIdsByRole = [];

            foreach ($resolved as $role => $entry) {
                $areaIdsByRole[$role] = (string) $entry['area']->getKey();
            }

            $areaDepth = -1;

            foreach ($hierarchy->levels as $level) {
                if ($level->kind !== 'area') {
                    continue;
                }

                $areaDepth++;
                $role = CountryAddressProfileResolver::roleForLevel($hierarchy, $level);

                if (isset($resolved[$role])) {
                    continue;
                }

                $types = $this->levelTypes($level);
                $levels = $this->levelLevels($level);

                if ($types === []) {
                    continue;
                }

                $parentId = $profiles->parentAreaIdForRole(
                    $countryId,
                    $role,
                    $stateId,
                    $areaIdsByRole,
                    fn (string $parentRole, string $candidateParentId): bool => $this->matcher->scopedAreaQuery(
                        $countryId,
                        $candidateParentId,
                        $types,
                        $levels !== [] ? $levels : null,
                    )->exists(),
                );
                $refinedParentId = is_string($level->refinedBy)
                    ? ($areaIdsByRole[$level->refinedBy] ?? null)
                    : null;

                if (
                    $parentId === null
                    || ($refinedParentId !== null && $parentId !== $refinedParentId)
                ) {
                    continue;
                }

                $componentTypes = GooglePlaceComponentMapper::componentTypesForLevel($hierarchy, $areaDepth);
                $names = $components->candidateNames($componentTypes);
                $area = null;

                foreach ($names as $name) {
                    $area = $this->matcher->resolveArea(
                        $name,
                        $countryId,
                        $parentId,
                        $types,
                        $levels !== [] ? $levels : null,
                        $countryCode,
                    );

                    if ($area instanceof AddressArea) {
                        break;
                    }
                }

                $inferredFromPlaceName = false;

                if (! $area instanceof AddressArea && $names === [] && ! $structuralResolved) {
                    $area = $this->matcher->resolveAreaFromPlaceName(
                        $placeName,
                        $countryId,
                        $parentId,
                        $countryCode,
                        $types,
                        $levels,
                    );
                    $inferredFromPlaceName = $area instanceof AddressArea;
                }

                if (! $area instanceof AddressArea) {
                    continue;
                }

                $resolved[$role] = ['hierarchy' => $hierarchy, 'level' => $level, 'area' => $area];
                $resolvedRolesByHierarchy[$hierarchy->key][] = $role;
                $areaIdsByRole[$role] = (string) $area->getKey();

                if ($inferredFromPlaceName) {
                    $inferredAreaRoles[] = $role;
                    unset($misses[$role]);
                }
            }

            if ($structuralResolved) {
                continue;
            }

            $areaDepth = -1;

            foreach ($hierarchy->levels as $level) {
                if ($level->kind !== 'area') {
                    continue;
                }

                $areaDepth++;
                $role = CountryAddressProfileResolver::roleForLevel($hierarchy, $level);

                if (isset($resolved[$role])) {
                    continue;
                }

                $types = $this->levelTypes($level);

                if ($types === []) {
                    continue;
                }

                $names = $components->candidateNames(
                    GooglePlaceComponentMapper::componentTypesForLevel($hierarchy, $areaDepth),
                );

                if ($names === []) {
                    continue;
                }

                $area = $this->matchLevel(
                    $names,
                    $countryId,
                    $countryCode,
                    $this->deepestResolvedId($hierarchy->key, $resolved, $resolvedRolesByHierarchy)
                        ?? ($areaTreeRoot instanceof AddressArea ? (string) $areaTreeRoot->getKey() : null),
                    $areaTreeRoot,
                    $hierarchy,
                    $level,
                );

                if ($area instanceof AddressArea) {
                    $resolved[$role] = ['hierarchy' => $hierarchy, 'level' => $level, 'area' => $area];
                    $resolvedRolesByHierarchy[$hierarchy->key][] = $role;
                } elseif ($names !== []) {
                    $misses[$role] ??= $names;
                }
            }
        }
    }

    /**
     * @param  list<string>  $names
     */
    private function matchLevel(
        array $names,
        ?string $countryId,
        ?string $countryCode,
        ?string $parentId,
        ?AddressArea $areaTreeRoot,
        AddressHierarchyDefinition $hierarchy,
        AddressLevelDefinition $level,
    ): ?AddressArea {
        $types = $this->levelTypes($level);
        $levels = $this->levelLevels($level);

        foreach ($names as $name) {
            $area = $this->matcher->resolveArea($name, $countryId, $parentId, $types, $levels, $countryCode);

            if ($area instanceof AddressArea) {
                return $area;
            }
        }

        if ($areaTreeRoot instanceof AddressArea) {
            $hierarchyType = $level->hierarchyType ?? $hierarchy->key;

            foreach ($names as $name) {
                $candidate = $this->addressAreaHierarchyResolver->resolveWithinHierarchy(
                    $name,
                    $countryId,
                    (string) $areaTreeRoot->getKey(),
                    $hierarchyType,
                    $types,
                );

                if ($candidate instanceof AddressArea && $this->areaMatchesLevel($candidate, $level)) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    /**
     * @param  array<string, array{hierarchy: AddressHierarchyDefinition, level: AddressLevelDefinition, area: AddressArea}>  $resolved
     * @param  array<string, list<string>>  $resolvedRolesByHierarchy
     */
    private function resolvePostalFromPostcode(
        ?string $postcode,
        ?string $countryId,
        ?string $countryCode,
        AddressHierarchyDefinition $hierarchy,
        array &$resolved,
        array &$resolvedRolesByHierarchy,
    ): void {
        $code = is_string($postcode) ? mb_strtoupper(mb_trim($postcode)) : '';

        if ($code === '' || $countryCode === null) {
            return;
        }

        $candidateIds = $this->postalAreas->candidateAreaIds($countryCode, $code, $countryId);
        $candidates = ModelResolver::areaClass()::query()
            ->whereIn('id', $candidateIds)
            ->get();

        foreach ($hierarchy->levels as $level) {
            if ($level->kind !== 'area') {
                continue;
            }

            $role = CountryAddressProfileResolver::roleForLevel($hierarchy, $level);

            if (isset($resolved[$role])) {
                continue;
            }

            $matches = $candidates
                ->filter(fn (AddressArea $area): bool => $this->areaMatchesLevel($area, $level))
                ->values();

            if ($matches->count() === 1 && $matches->first() instanceof AddressArea) {
                $resolved[$role] = ['hierarchy' => $hierarchy, 'level' => $level, 'area' => $matches->first()];
                $resolvedRolesByHierarchy[$hierarchy->key][] = $role;
            }
        }
    }

    /**
     * @param  list<AddressHierarchyDefinition>  $hierarchies
     * @param  array<string, array{hierarchy: AddressHierarchyDefinition, level: AddressLevelDefinition, area: AddressArea}>  $resolved
     * @param  array<string, list<string>>  $resolvedRolesByHierarchy
     */
    private function recoverAncestors(array $hierarchies, array &$resolved, array &$resolvedRolesByHierarchy): void
    {
        foreach ($hierarchies as $hierarchy) {
            $areaLevels = array_values(array_filter(
                $hierarchy->levels,
                static fn (AddressLevelDefinition $level): bool => $level->kind === 'area',
            ));

            for ($index = count($areaLevels) - 1; $index >= 0; $index--) {
                $role = CountryAddressProfileResolver::roleForLevel($hierarchy, $areaLevels[$index]);

                if (isset($resolved[$role])) {
                    continue;
                }

                for ($deeper = $index + 1; $deeper < count($areaLevels); $deeper++) {
                    $deeperRole = CountryAddressProfileResolver::roleForLevel($hierarchy, $areaLevels[$deeper]);

                    if (! isset($resolved[$deeperRole])) {
                        continue;
                    }

                    $ancestor = $this->addressAreaHierarchyResolver->ancestorOfTypes(
                        $resolved[$deeperRole]['area'],
                        $this->levelTypes($areaLevels[$index]),
                        $areaLevels[$index]->hierarchyType ?? $hierarchy->key,
                    );

                    if ($ancestor instanceof AddressArea && $this->areaMatchesLevel($ancestor, $areaLevels[$index])) {
                        $resolved[$role] = ['hierarchy' => $hierarchy, 'level' => $areaLevels[$index], 'area' => $ancestor];
                        $resolvedRolesByHierarchy[$hierarchy->key][] = $role;

                        break;
                    }
                }
            }
        }
    }

    /**
     * @param  list<AddressHierarchyDefinition>  $hierarchies
     * @param  list<string>  $stateTypes
     * @param  array<string, array{hierarchy: AddressHierarchyDefinition, level: AddressLevelDefinition, area: AddressArea}>  $resolved
     */
    private function recoverAreaTreeRoot(
        array $hierarchies,
        ?AddressLevelDefinition $stateLevel,
        array $stateTypes,
        array $resolved,
    ): ?AddressArea {
        if ($stateTypes === []) {
            return null;
        }

        $hierarchyKeys = [];

        foreach ($hierarchies as $hierarchy) {
            foreach ($hierarchy->levels as $level) {
                if ($level === $stateLevel) {
                    $hierarchyKeys[] = $level->hierarchyType ?? $hierarchy->key;
                }
            }

            $hierarchyKeys[] = $hierarchy->key;
        }

        $hierarchyKeys = array_values(array_unique($hierarchyKeys));

        if ($hierarchyKeys === []) {
            $hierarchyKeys = ['administrative'];
        }

        foreach ($this->resolvedEntriesInOrder($hierarchies, $resolved) as $entry) {
            foreach ($hierarchyKeys as $hierarchyKey) {
                $root = $this->addressAreaHierarchyResolver->ancestorOfTypes($entry['area'], $stateTypes, $hierarchyKey);

                if ($root instanceof AddressArea) {
                    return $root;
                }
            }
        }

        return null;
    }

    /**
     * @param  list<AddressHierarchyDefinition>  $hierarchies
     * @param  array<string, array{hierarchy: AddressHierarchyDefinition, level: AddressLevelDefinition, area: AddressArea}>  $resolved
     */
    private function recoverStateId(array $hierarchies, array $resolved): ?string
    {
        foreach ($this->resolvedEntriesInOrder($hierarchies, $resolved) as $entry) {
            $stateId = AddressAreaStateBridge::stateIdForArea($entry['area']);

            if ($stateId !== null) {
                return $stateId;
            }
        }

        return null;
    }

    /**
     * @param  list<AddressHierarchyDefinition>  $hierarchies
     * @param  array<string, array{hierarchy: AddressHierarchyDefinition, level: AddressLevelDefinition, area: AddressArea}>  $resolved
     * @return list<array{hierarchy: AddressHierarchyDefinition, level: AddressLevelDefinition, area: AddressArea}>
     */
    private function resolvedEntriesInOrder(array $hierarchies, array $resolved): array
    {
        $ordered = [];

        foreach ($hierarchies as $hierarchy) {
            foreach ($hierarchy->levels as $level) {
                if ($level->kind !== 'area') {
                    continue;
                }

                $role = CountryAddressProfileResolver::roleForLevel($hierarchy, $level);

                if (isset($resolved[$role])) {
                    $ordered[] = $resolved[$role];
                }
            }
        }

        return $ordered;
    }

    /**
     * @param  array<string, array{hierarchy: AddressHierarchyDefinition, level: AddressLevelDefinition, area: AddressArea}>  $resolved
     * @param  array<string, list<string>>  $resolvedRolesByHierarchy
     */
    private function deepestResolvedId(string $hierarchyKey, array $resolved, array $resolvedRolesByHierarchy): ?string
    {
        $roles = $resolvedRolesByHierarchy[$hierarchyKey] ?? [];

        if ($roles === []) {
            return null;
        }

        $last = end($roles);

        return is_string($last) && isset($resolved[$last])
            ? (string) $resolved[$last]['area']->getKey()
            : null;
    }

    /**
     * @param  list<AddressHierarchyDefinition>  $hierarchies
     * @param  array<string, string>  $assignments
     * @return array<string, string>
     */
    private function orderAssignments(array $hierarchies, array $assignments): array
    {
        $ordered = [];

        foreach ($hierarchies as $hierarchy) {
            foreach ($hierarchy->levels as $level) {
                if ($level->kind !== 'area') {
                    continue;
                }

                $role = CountryAddressProfileResolver::roleForLevel($hierarchy, $level);

                if (isset($assignments[$role])) {
                    $ordered[$role] = $assignments[$role];
                }
            }
        }

        foreach ($assignments as $role => $areaId) {
            $ordered[$role] ??= $areaId;
        }

        return $ordered;
    }

    /**
     * @return list<string>
     */
    private function stateAreaTypes(?AddressLevelDefinition $stateLevel): array
    {
        if ($stateLevel instanceof AddressLevelDefinition) {
            if ($stateLevel->areaTypes !== []) {
                return $stateLevel->areaTypes;
            }

            if ($stateLevel->areaType !== null) {
                return [$stateLevel->areaType];
            }

            return [];
        }

        return [];
    }

    /**
     * @return list<string>
     */
    private function levelTypes(AddressLevelDefinition $level): array
    {
        if ($level->areaTypes !== []) {
            return $level->areaTypes;
        }

        if ($level->areaType !== null) {
            return [$level->areaType];
        }

        return [];
    }

    /**
     * @return list<int>
     */
    private function levelLevels(AddressLevelDefinition $level): array
    {
        if ($level->areaLevels !== []) {
            return $level->areaLevels;
        }

        if ($level->areaLevel !== null) {
            return [$level->areaLevel];
        }

        return [];
    }

    private function areaMatchesLevel(AddressArea $area, AddressLevelDefinition $level): bool
    {
        $types = $this->levelTypes($level);
        $levels = $this->levelLevels($level);

        return ($types === [] || in_array($area->type, $types, true))
            && ($levels === [] || in_array($area->level, $levels, true));
    }

    /**
     * @param  list<mixed>  $candidates
     */
    private function preferredString(array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }
}
