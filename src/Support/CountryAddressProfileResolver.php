<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Contracts\CountryAddressProfile;
use AIArmada\Addressing\Contracts\CountryAreaTypeLabelProvider;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class CountryAddressProfileResolver
{
    private const string REQUEST_CACHE_KEY = 'aiarmada.addressing.country-profiles';

    public function __construct(
        private readonly Container $container,
        private readonly AddressCountryResolver $countryResolver,
    ) {}

    public function resolve(mixed $country): ?CountryAddressProfile
    {
        $cacheKey = $this->cacheKey($country);

        if ($cacheKey !== null && app()->bound('request')) {
            $request = request();
            $cache = $request->attributes->get(self::REQUEST_CACHE_KEY, []);

            if (is_array($cache) && array_key_exists($cacheKey, $cache)) {
                $profile = $cache[$cacheKey];

                return $profile instanceof CountryAddressProfile ? $profile : null;
            }
        }

        $resolvedCountry = $this->countryResolver->resolve($country);

        if (! $resolvedCountry instanceof AddressCountry) {
            $this->cache($cacheKey, null);

            return null;
        }

        $countryCode = mb_strtoupper((string) $resolvedCountry->iso2);

        foreach (config('addressing.geography.providers', []) as $providerClass) {
            if (! is_string($providerClass)) {
                throw new InvalidArgumentException('Addressing geography providers must be class strings.');
            }

            $provider = $this->container->make($providerClass);

            if (! $provider instanceof CountryAddressProfile) {
                continue;
            }

            if (mb_strtoupper(mb_trim($provider->countryCode())) === $countryCode) {
                $this->cache($cacheKey, $provider);

                return $provider;
            }
        }

        $this->cache($cacheKey, null);

        return null;
    }

    /** @return list<AddressHierarchyDefinition> */
    public function hierarchies(mixed $country): array
    {
        return $this->resolve($country)?->addressHierarchies() ?? [];
    }

    /**
     * Return the country's state-kind (region) level, if its profile defines one.
     *
     * Region levels carry no assignment role because a State is not an area
     * assignment, so consumers label the State selector through this lookup
     * instead of matching a role. When several hierarchies define one, the
     * first wins; providers list their primary hierarchy first.
     */
    public function stateLevel(mixed $country): ?AddressLevelDefinition
    {
        return $this->stateDefinition($country)['level'] ?? null;
    }

    /**
     * Resolve an assignment role to its hierarchy and level definition.
     *
     * The 'state_id' pseudo-role resolves to the first state-kind level,
     * since those levels carry no assignment role. Unknown countries and
     * unknown roles return null.
     *
     * @return array{hierarchy: AddressHierarchyDefinition, level: AddressLevelDefinition}|null
     */
    public function definitionForRole(mixed $country, string $role): ?array
    {
        if ($role === 'state_id') {
            return $this->stateDefinition($country);
        }

        foreach ($this->hierarchies($country) as $hierarchy) {
            foreach ($hierarchy->levels as $level) {
                if ($level->kind !== 'state' && self::roleForLevel($hierarchy, $level) === $role) {
                    return ['hierarchy' => $hierarchy, 'level' => $level];
                }
            }
        }

        return null;
    }

    public function levelForRole(mixed $country, string $role): ?AddressLevelDefinition
    {
        return $this->definitionForRole($country, $role)['level'] ?? null;
    }

    public static function roleForLevel(AddressHierarchyDefinition $hierarchy, AddressLevelDefinition $level): string
    {
        return $level->assignmentRole ?? "{$hierarchy->key}_{$level->key}";
    }

    /**
     * Ordered assignment roles for a country, in provider hierarchy order.
     *
     * Providers list their primary hierarchy first, so this order is the
     * canonical cascade order consumers should render.
     *
     * @return list<string>
     */
    public function assignmentRoles(mixed $country): array
    {
        $roles = [];

        foreach ($this->hierarchies($country) as $hierarchy) {
            foreach ($hierarchy->levels as $level) {
                if ($level->kind === 'state') {
                    continue;
                }

                $roles[] = self::roleForLevel($hierarchy, $level);
            }
        }

        return array_values(array_unique($roles));
    }

    public function hierarchy(mixed $country, string $key): ?AddressHierarchyDefinition
    {
        foreach ($this->hierarchies($country) as $hierarchy) {
            if ($hierarchy->key === $key) {
                return $hierarchy;
            }
        }

        return null;
    }

    /**
     * First hierarchy carrying selectable area levels.
     */
    public function firstAreaHierarchy(mixed $country): ?AddressHierarchyDefinition
    {
        foreach ($this->hierarchies($country) as $hierarchy) {
            foreach ($hierarchy->levels as $level) {
                if ($level->kind === 'area') {
                    return $hierarchy;
                }
            }
        }

        return null;
    }

    /**
     * Declared parent level of a role, resolved within the role's own hierarchy.
     */
    public function parentLevel(mixed $country, string $role): ?AddressLevelDefinition
    {
        $definition = $this->definitionForRole($country, $role);

        if ($definition === null || $definition['level']->parentKey === null) {
            return null;
        }

        foreach ($definition['hierarchy']->levels as $level) {
            if ($level->key === $definition['level']->parentKey) {
                return $level;
            }
        }

        return null;
    }

    /**
     * Resolve the area id scoping a role's options.
     *
     * Declared chain parents resolve to the selected id directly.
     * Region-parented roles narrow first to an explicitly refinedBy role
     * (a picked district narrows postal localities to its own rows), then
     * to the nearest preceding selected level when the probe proves it
     * yields options, falling back to the declared state parent otherwise
     * so district-less states keep working. Levels without a matching
     * parent/child link in the data never narrow, so unrelated sibling
     * levels keep state scope.
     *
     * @param  array<string, ?string>  $areaIdsByRole
     * @param  ?callable(string $role, string $parentId): bool  $hasOptions
     */
    public function parentAreaIdForRole(mixed $country, string $role, mixed $stateId, array $areaIdsByRole, ?callable $hasOptions = null): ?string
    {
        $definition = $this->definitionForRole($country, $role);

        if ($definition === null) {
            return null;
        }

        $parent = $this->parentLevel($country, $role);

        if ($parent === null) {
            return null;
        }

        if ($parent->kind !== 'state') {
            return self::stringOrNull($areaIdsByRole[self::roleForLevel($definition['hierarchy'], $parent)] ?? null);
        }

        $probe = $hasOptions ?? fn (string $probeRole, string $probeParentId): bool => $this->defaultHasOptions($country, $probeRole, $probeParentId);
        $refinedBy = $definition['level']->refinedBy;

        if ($refinedBy !== null) {
            $selected = self::stringOrNull($areaIdsByRole[$refinedBy] ?? null);

            if ($selected !== null && $probe($role, $selected)) {
                return $selected;
            }
        }

        $levels = array_values($definition['hierarchy']->levels);
        $position = null;

        foreach ($levels as $index => $level) {
            if ($level->key === $definition['level']->key) {
                $position = $index;

                break;
            }
        }

        if ($position !== null) {
            for ($index = $position - 1; $index >= 0; $index--) {
                $candidate = $levels[$index];

                if ($candidate->kind !== 'area') {
                    continue;
                }

                $selected = self::stringOrNull($areaIdsByRole[self::roleForLevel($definition['hierarchy'], $candidate)] ?? null);

                if ($selected === null) {
                    continue;
                }

                if ($probe($role, $selected)) {
                    return $selected;
                }
            }
        }

        return AddressAreaStateBridge::areaIdForState(
            self::stringOrNull($stateId),
            self::hierarchyType($definition['hierarchy'], $definition['level']),
        );
    }

    /**
     * Roles that reset when the given role changes: declared descendants,
     * levels refined by it, plus region-parented levels positioned after
     * it, mirroring parentAreaIdForRole(). A new district invalidates any
     * subdivision or locality picked under the previous one.
     *
     * @return list<string>
     */
    public function successorRoles(mixed $country, string $role): array
    {
        $changed = $this->definitionForRole($country, $role);

        if ($changed === null) {
            return [];
        }

        $successors = [];

        foreach ($this->assignmentRoles($country) as $candidate) {
            if ($candidate === $role) {
                continue;
            }

            $definition = $this->definitionForRole($country, $candidate);

            if ($definition === null) {
                continue;
            }

            if ($definition['level']->refinedBy === $role) {
                $successors[] = $candidate;

                continue;
            }

            if ($definition['hierarchy']->key !== $changed['hierarchy']->key) {
                continue;
            }

            if (self::ancestorMatches($definition['hierarchy'], $definition['level'], static fn (AddressLevelDefinition $ancestor): bool => $ancestor->key === $changed['level']->key)) {
                $successors[] = $candidate;
            } elseif (self::isNarrowedSuccessor($changed, $definition)) {
                $successors[] = $candidate;
            }
        }

        return $successors;
    }

    /**
     * Roles whose parent chain passes through the state level.
     *
     * @return list<string>
     */
    public function stateDependentRoles(mixed $country): array
    {
        $dependents = [];

        foreach ($this->assignmentRoles($country) as $role) {
            $definition = $this->definitionForRole($country, $role);

            if ($definition === null) {
                continue;
            }

            if (self::ancestorMatches($definition['hierarchy'], $definition['level'], static fn (AddressLevelDefinition $ancestor): bool => $ancestor->kind === 'state')) {
                $dependents[] = $role;
            }
        }

        return $dependents;
    }

    /**
     * Level gating a role's selector: the declared parent, except
     * region-parented roles gate on an explicitly refinedBy level or the
     * nearest preceding area level when stored links prove the narrowing
     * is structural in the selected state.
     *
     * Malaysia's subdivisions and localities gate on the district in Johor
     * (districts own mukim and locality rows there) but stay state-gated
     * in KL (no districts exist), so district-less states keep working.
     *
     * @param  ?callable(string $role, string $ancestorRole, ?string $stateId): bool  $hasStructuralLinks
     */
    public function effectiveParentLevel(mixed $country, string $role, mixed $stateId, ?callable $hasStructuralLinks = null): ?AddressLevelDefinition
    {
        $definition = $this->definitionForRole($country, $role);

        if ($definition === null) {
            return null;
        }

        $parent = $this->parentLevel($country, $role);

        if ($parent === null || $parent->kind !== 'state') {
            return $parent;
        }

        $resolvedStateId = self::stringOrNull($stateId);
        $check = $hasStructuralLinks ?? fn (string $checkRole, string $checkAncestor, ?string $checkState): bool => $this->defaultHasStructuralLinks($country, $checkRole, $checkAncestor, $checkState);
        $refinedBy = $definition['level']->refinedBy;

        if ($refinedBy !== null) {
            $refiner = $this->definitionForRole($country, $refinedBy);

            if ($refiner !== null && $check($role, $refinedBy, $resolvedStateId)) {
                return $refiner['level'];
            }
        }

        $levels = array_values($definition['hierarchy']->levels);
        $position = null;

        foreach ($levels as $index => $level) {
            if ($level->key === $definition['level']->key) {
                $position = $index;

                break;
            }
        }

        if ($position === null) {
            return $parent;
        }

        for ($index = $position - 1; $index >= 0; $index--) {
            $candidate = $levels[$index];

            if ($candidate->kind !== 'area') {
                continue;
            }

            if ($check($role, self::roleForLevel($definition['hierarchy'], $candidate), $resolvedStateId)) {
                return $candidate;
            }
        }

        return $parent;
    }

    /**
     * Whether subdivision + locality roles should share one grouped
     * control: the package default groups them, but only where both
     * roles resolve and gate on the same parent level, so the group
     * never mixes differently-scoped selectors. Apps override with
     * addressing.fields.group_subdivision_locality.
     *
     * @param  ?callable(string $role, string $ancestorRole, ?string $stateId): bool  $hasStructuralLinks
     */
    public function shouldGroupSubdivisionLocality(mixed $country, mixed $stateId, ?callable $hasStructuralLinks = null): bool
    {
        if (! config('addressing.fields.group_subdivision_locality', true)) {
            return false;
        }

        $subdivisionGate = $this->effectiveParentLevel($country, 'administrative_subdivision', $stateId, $hasStructuralLinks);
        $localityGate = $this->effectiveParentLevel($country, 'postal_locality', $stateId, $hasStructuralLinks);

        return $subdivisionGate !== null
            && $localityGate !== null
            && $subdivisionGate->key === $localityGate->key;
    }

    /**
     * Display label for a role, narrowed to the types present in scope.
     *
     * Without a state the static level label applies. With one, the label
     * joins the distinct area-type labels present under the role's resolved
     * parent (narrowed like the options themselves), so a Johor district
     * selector reads District, a Putrajaya locality selector reads Precinct,
     * and mixed scopes keep a combined label. Null when the country or role
     * is unknown.
     *
     * @param  array<string, ?string>  $areaIdsByRole
     */
    public function levelLabel(mixed $country, string $role, mixed $stateId = null, array $areaIdsByRole = []): ?string
    {
        $definition = $this->definitionForRole($country, $role);

        if ($definition === null) {
            return null;
        }

        $stateIdString = self::stringOrNull($stateId);

        if ($stateIdString === null) {
            return $definition['level']->label;
        }

        $parentId = $this->parentAreaIdForRole($country, $role, $stateIdString, $areaIdsByRole);

        if ($parentId === null) {
            return $definition['level']->label;
        }

        $types = $this->scopeTypes($definition, $parentId);

        if ($types === []) {
            return $definition['level']->label;
        }

        $profile = $this->resolve($country);
        $stateCode = $this->stateCode($stateIdString);
        $labels = [];

        foreach ($types as $type) {
            $label = $this->areaTypeLabel($profile, $stateCode, $type);

            if (! in_array($label, $labels, true)) {
                $labels[] = $label;
            }
        }

        return implode(' / ', $labels);
    }

    /** @return list<string> */
    public static function areaTypesForLevel(AddressLevelDefinition $level): array
    {
        return $level->areaTypes !== []
            ? $level->areaTypes
            : ($level->areaType !== null ? [$level->areaType] : []);
    }

    /** @return list<int> */
    public static function areaLevelsForLevel(AddressLevelDefinition $level): array
    {
        return $level->areaLevels !== []
            ? $level->areaLevels
            : ($level->areaLevel !== null ? [$level->areaLevel] : []);
    }

    public static function hierarchyType(AddressHierarchyDefinition $hierarchy, AddressLevelDefinition $level): string
    {
        return $level->hierarchyType ?? $hierarchy->key;
    }

    /** @return array{hierarchy: AddressHierarchyDefinition, level: AddressLevelDefinition}|null */
    private function stateDefinition(mixed $country): ?array
    {
        foreach ($this->hierarchies($country) as $hierarchy) {
            foreach ($hierarchy->levels as $level) {
                if ($level->kind === 'state') {
                    return ['hierarchy' => $hierarchy, 'level' => $level];
                }
            }
        }

        return null;
    }

    private function defaultHasOptions(mixed $country, string $role, string $parentId): bool
    {
        $definition = $this->definitionForRole($country, $role);

        if ($definition === null) {
            return false;
        }

        $areaClass = ModelResolver::areaClass();
        $areaTypes = self::areaTypesForLevel($definition['level']);
        $areaLevels = self::areaLevelsForLevel($definition['level']);
        $hierarchyType = self::hierarchyType($definition['hierarchy'], $definition['level']);

        return $areaClass::query()
            ->where('is_active', true)
            ->when($areaTypes !== [], static fn (EloquentBuilder $query): EloquentBuilder => $query->whereIn('type', $areaTypes))
            ->when($areaLevels !== [], static fn (EloquentBuilder $query): EloquentBuilder => $query->whereIn('level', $areaLevels))
            ->whereAncestorLink($parentId, $hierarchyType)
            ->exists();
    }

    private function defaultHasStructuralLinks(mixed $country, string $role, string $ancestorRole, ?string $stateId): bool
    {
        $definition = $this->definitionForRole($country, $role);
        $ancestor = $this->definitionForRole($country, $ancestorRole);

        if ($definition === null || $ancestor === null || $stateId === null) {
            return false;
        }

        $stateAreaId = AddressAreaStateBridge::areaIdForState(
            $stateId,
            self::hierarchyType($ancestor['hierarchy'], $ancestor['level']),
        );

        if ($stateAreaId === null) {
            return false;
        }

        $areaClass = ModelResolver::areaClass();
        $pivot = AddressingTableResolver::resolve('area_relationships');
        $areaTypes = self::areaTypesForLevel($definition['level']);
        $areaLevels = self::areaLevelsForLevel($definition['level']);
        $hierarchyType = self::hierarchyType($definition['hierarchy'], $definition['level']);
        $ancestorTypes = self::areaTypesForLevel($ancestor['level']);
        $ancestorLevels = self::areaLevelsForLevel($ancestor['level']);
        $ancestorHierarchyType = self::hierarchyType($ancestor['hierarchy'], $ancestor['level']);

        $query = $areaClass::query()
            ->where('is_active', true)
            ->when($areaTypes !== [], static fn (EloquentBuilder $scoped): EloquentBuilder => $scoped->whereIn('type', $areaTypes))
            ->when($areaLevels !== [], static fn (EloquentBuilder $scoped): EloquentBuilder => $scoped->whereIn('level', $areaLevels));

        $keyName = $query->getModel()->getQualifiedKeyName();

        return $query
            ->whereExists(function (QueryBuilder $exists) use ($pivot, $keyName, $hierarchyType, $stateAreaId, $ancestorTypes, $ancestorLevels, $ancestorHierarchyType): void {
                $ancestorClass = ModelResolver::areaClass();

                $exists->selectRaw('1')
                    ->from($pivot)
                    ->whereColumn($pivot . '.child_address_area_id', $keyName)
                    ->where($pivot . '.relationship_type', 'contains')
                    ->where($pivot . '.hierarchy_type', $hierarchyType)
                    ->whereIn($pivot . '.parent_address_area_id', $ancestorClass::query()
                        ->where('is_active', true)
                        ->when($ancestorTypes !== [], static fn (EloquentBuilder $scoped): EloquentBuilder => $scoped->whereIn('type', $ancestorTypes))
                        ->when($ancestorLevels !== [], static fn (EloquentBuilder $scoped): EloquentBuilder => $scoped->whereIn('level', $ancestorLevels))
                        ->whereAncestorLink($stateAreaId, $ancestorHierarchyType)
                        ->select('id'))
                    ->where(static function (QueryBuilder $valid) use ($pivot): void {
                        $valid->whereNull($pivot . '.valid_from')->orWhereDate($pivot . '.valid_from', '<=', CarbonImmutable::now());
                    })
                    ->where(static function (QueryBuilder $valid) use ($pivot): void {
                        $valid->whereNull($pivot . '.valid_until')->orWhereDate($pivot . '.valid_until', '>=', CarbonImmutable::now());
                    });
            })
            ->exists();
    }

    /**
     * Region-parented levels positioned after the changed level reset with
     * it, mirroring parentAreaIdForRole(): a new district invalidates any
     * subdivision picked under the previous one.
     *
     * @param  array{hierarchy: AddressHierarchyDefinition, level: AddressLevelDefinition}  $changed
     * @param  array{hierarchy: AddressHierarchyDefinition, level: AddressLevelDefinition}  $definition
     */
    private static function isNarrowedSuccessor(array $changed, array $definition): bool
    {
        if ($definition['hierarchy']->key !== $changed['hierarchy']->key || $changed['level']->kind !== 'area') {
            return false;
        }

        $reachesState = self::ancestorMatches(
            $definition['hierarchy'],
            $definition['level'],
            static fn (AddressLevelDefinition $ancestor): bool => $ancestor->kind === 'state',
        );

        if (! $reachesState) {
            return false;
        }

        $changedPosition = null;
        $candidatePosition = null;

        foreach (array_values($definition['hierarchy']->levels) as $index => $level) {
            if ($level->key === $changed['level']->key) {
                $changedPosition = $index;
            }

            if ($level->key === $definition['level']->key) {
                $candidatePosition = $index;
            }
        }

        return $changedPosition !== null && $candidatePosition !== null && $changedPosition < $candidatePosition;
    }

    /** @param callable(AddressLevelDefinition): bool $matches */
    private static function ancestorMatches(AddressHierarchyDefinition $hierarchy, AddressLevelDefinition $level, callable $matches): bool
    {
        $byKey = [];

        foreach ($hierarchy->levels as $candidate) {
            $byKey[$candidate->key] = $candidate;
        }

        $visited = [];
        $current = $level;

        while (true) {
            $parentKey = $current->parentKey;

            if ($parentKey === null || isset($visited[$current->key])) {
                return false;
            }

            $visited[$current->key] = true;
            $parent = $byKey[$parentKey] ?? null;

            if (! $parent instanceof AddressLevelDefinition) {
                return false;
            }

            if ($matches($parent)) {
                return true;
            }

            $current = $parent;
        }
    }

    private static function stringOrNull(mixed $value): ?string
    {
        if (is_int($value)) {
            $value = (string) $value;
        }

        if (! is_string($value)) {
            return null;
        }

        $value = mb_trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * Distinct area types in scope, ordered by the level's type order.
     *
     * @param  array{hierarchy: AddressHierarchyDefinition, level: AddressLevelDefinition}  $definition
     * @return list<string>
     */
    private function scopeTypes(array $definition, string $parentId): array
    {
        $areaClass = ModelResolver::areaClass();
        $areaTypes = self::areaTypesForLevel($definition['level']);
        $areaLevels = self::areaLevelsForLevel($definition['level']);

        $found = $areaClass::query()
            ->where('is_active', true)
            ->when($areaTypes !== [], static fn (EloquentBuilder $query): EloquentBuilder => $query->whereIn('type', $areaTypes))
            ->when($areaLevels !== [], static fn (EloquentBuilder $query): EloquentBuilder => $query->whereIn('level', $areaLevels))
            ->whereAncestorLink($parentId, self::hierarchyType($definition['hierarchy'], $definition['level']))
            ->distinct()
            ->pluck('type')
            ->map(static fn (mixed $type): string => (string) $type)
            ->all();

        $order = array_flip($areaTypes);

        usort($found, static fn (string $a, string $b): int => ($order[$a] ?? PHP_INT_MAX) <=> ($order[$b] ?? PHP_INT_MAX));

        return array_values(array_unique($found));
    }

    private function areaTypeLabel(?CountryAddressProfile $profile, ?string $stateCode, string $type): string
    {
        if ($profile instanceof CountryAreaTypeLabelProvider) {
            if ($stateCode !== null) {
                foreach ($profile->stateAreaTypeLabels() as $override) {
                    if (($override['state_code'] ?? null) === $stateCode && isset($override['type_labels'][$type])) {
                        return $override['type_labels'][$type];
                    }
                }
            }

            $base = $profile->areaTypeLabels();

            if (isset($base[$type])) {
                return $base[$type];
            }
        }

        return Str::headline($type);
    }

    private function stateCode(string $stateId): ?string
    {
        $key = 'state-code:' . $stateId;

        if (app()->bound('request')) {
            $cache = request()->attributes->get(self::REQUEST_CACHE_KEY, []);

            if (is_array($cache) && array_key_exists($key, $cache)) {
                $cached = $cache[$key];

                return is_string($cached) ? $cached : null;
            }
        }

        $stateClass = ModelResolver::stateClass();
        $code = $stateClass::query()->whereKey($stateId)->value('code');
        $code = is_scalar($code) ? self::stringOrNull((string) $code) : null;

        if (app()->bound('request')) {
            $request = request();
            $cache = $request->attributes->get(self::REQUEST_CACHE_KEY, []);
            $cache = is_array($cache) ? $cache : [];
            $cache[$key] = $code;
            $request->attributes->set(self::REQUEST_CACHE_KEY, $cache);
        }

        return $code;
    }

    private function cacheKey(mixed $country): ?string
    {
        if ($country instanceof AddressCountry) {
            return 'id:' . (string) $country->getKey();
        }

        if (! is_scalar($country)) {
            return null;
        }

        $value = mb_trim((string) $country);

        return $value === '' ? null : 'value:' . mb_strtolower($value);
    }

    private function cache(?string $key, ?CountryAddressProfile $profile): void
    {
        if ($key === null || ! app()->bound('request')) {
            return;
        }

        $request = request();
        $cache = $request->attributes->get(self::REQUEST_CACHE_KEY, []);
        $cache = is_array($cache) ? $cache : [];
        $cache[$key] = $profile;
        $request->attributes->set(self::REQUEST_CACHE_KEY, $cache);
    }
}
