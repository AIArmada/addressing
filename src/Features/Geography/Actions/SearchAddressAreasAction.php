<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\Geography\Actions;

use AIArmada\Addressing\Contracts\AddressAreaSearch;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryPostalCodeNormalizer;
use AIArmada\Addressing\Contracts\ResolutionGapReporter;
use AIArmada\Addressing\Data\AddressAreaMatchData;
use AIArmada\Addressing\Features\Geography\Models\AddressArea;
use AIArmada\Addressing\Support\ModelResolver;
use AIArmada\CommerceSupport\Support\LikeSearch;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class SearchAddressAreasAction implements AddressAreaSearch
{
    /** @var array<string, ?class-string> */
    private array $postalCodeNormalizers = [];

    /** @var array<array-key, mixed>|null */
    private ?array $configuredProviders = null;

    public function __construct(private readonly Container $container) {}

    /**
     * @return list<AddressAreaMatchData>
     */
    public function execute(
        string $query,
        ?string $countryCode = null,
        ?string $role = null,
        ?string $type = null,
        ?string $parentId = null,
        ?string $hierarchyType = null,
        ?string $postalCode = null,
        int $limit = 25,
    ): array {
        $query = mb_trim($query);
        $limit = max(1, min(100, $limit));

        if ($query === '' || mb_strlen($query) > 255) {
            return [];
        }

        $countryCode = $countryCode === null ? null : mb_strtoupper(mb_trim($countryCode));
        $needle = mb_strtolower($query);
        $escapedNeedle = LikeSearch::escape($needle);
        $containsPattern = "%{$escapedNeedle}%";
        $prefixPattern = "{$escapedNeedle}%";

        if ($hierarchyType !== null && mb_trim($hierarchyType) === '') {
            return [];
        }

        $areaClass = ModelResolver::areaClass();
        $escapeClause = LikeSearch::escapeClause($areaClass::query()->getConnection());

        $results = $areaClass::query()
            ->where('is_active', true)
            ->when($countryCode !== null, fn (Builder $builder): Builder => $builder->where('country_code', mb_strtoupper($countryCode)))
            ->when($type !== null, fn (Builder $builder): Builder => $builder->where('type', $type))
            ->when($parentId !== null && $hierarchyType === null, fn (Builder $builder): Builder => $builder->where('parent_id', $parentId))
            ->when($parentId !== null && $hierarchyType !== null, /** @param Builder<AddressArea> $builder */ function (Builder $builder) use ($parentId, $hierarchyType): Builder {
                return $builder->whereAncestorLink($parentId, $hierarchyType);
            })
            ->when($hierarchyType !== null && $parentId === null, /** @param Builder<AddressArea> $builder */ function (Builder $builder) use ($hierarchyType): Builder {
                return $builder->where(/** @param Builder<AddressArea> $hierarchyQuery */ function (Builder $hierarchyQuery) use ($hierarchyType): void {
                    $hierarchyQuery
                        ->whereAncestorLink(null, $hierarchyType)
                        ->orWhere(/** @param Builder<AddressArea> $roots */ static function (Builder $roots): void {
                            $roots->whereAncestorLinkMissing();
                        });
                });
            })
            ->when($postalCode !== null, fn (Builder $builder): Builder => $builder->whereHas('postalCodes', fn (Builder $codes): Builder => $codes->whereIn('code', $this->postalCodeLookupKeys($postalCode, $countryCode))))
            ->when($role !== null, fn (Builder $builder): Builder => $builder->whereHas('roles', fn (Builder $roles): Builder => $roles->where('role', $role)))
            ->where(function (Builder $builder) use ($containsPattern, $needle, $escapeClause): void {
                if (mb_strlen($needle) < 3) {
                    $builder
                        ->whereRaw('LOWER(name) = ?', [$needle])
                        ->orWhereRaw('LOWER(slug) = ?', [$needle])
                        ->orWhereHas('names', fn (Builder $names): Builder => $names->whereRaw('LOWER(name) = ?', [$needle]));

                    return;
                }

                $builder
                    ->whereRaw("LOWER(name) LIKE ? {$escapeClause}", [$containsPattern])
                    ->orWhereRaw("LOWER(slug) LIKE ? {$escapeClause}", [$containsPattern])
                    ->orWhereHas('names', fn (Builder $names): Builder => $names->whereRaw("LOWER(name) LIKE ? {$escapeClause}", [$containsPattern]));
            })
            ->orderByRaw("CASE WHEN LOWER(name) = ? THEN 0 WHEN LOWER(name) LIKE ? {$escapeClause} THEN 1 ELSE 2 END", [$needle, $prefixPattern])
            ->orderBy('name')
            ->orderBy((new $areaClass)->getQualifiedKeyName())
            ->limit($limit)
            ->get(['id', 'country_id', 'country_code', 'parent_id', 'type', 'level', 'name', 'native_name', 'code', 'slug', 'source', 'source_id', 'latitude', 'longitude']);

        $this->logGapOnMiss($query, $countryCode, $role, $type, $parentId, $hierarchyType, $postalCode, $results);

        return $results->map(static fn (AddressArea $area): AddressAreaMatchData => new AddressAreaMatchData(
            id: (string) $area->getKey(),
            countryId: $area->country_id,
            countryCode: $area->country_code,
            parentId: $area->parent_id,
            type: $area->type,
            level: $area->level,
            name: $area->name,
            nativeName: $area->native_name,
            code: $area->code,
            slug: $area->slug,
            source: $area->source,
            sourceId: $area->source_id,
            latitude: $area->latitude === null ? null : (float) $area->latitude,
            longitude: $area->longitude === null ? null : (float) $area->longitude,
        ))->all();
    }

    /**
     * Record unfiltered misses as corpus telemetry. Filter-scoped searches
     * carry their own narrowing intent, so misses there are not corpus gaps.
     *
     * @param  Collection<int, AddressArea>  $results
     */
    private function logGapOnMiss(
        string $query,
        ?string $countryCode,
        ?string $role,
        ?string $type,
        ?string $parentId,
        ?string $hierarchyType,
        ?string $postalCode,
        Collection $results,
    ): void {
        if ($results->isNotEmpty()) {
            return;
        }

        if ($countryCode === null || mb_strlen(mb_trim($countryCode)) !== 2) {
            return;
        }

        if (mb_strlen($query) < 3 || mb_strlen($query) > 255) {
            return;
        }

        if ($role !== null && (mb_trim($role) === '' || mb_strlen($role) > 50)) {
            return;
        }

        if ($type !== null || $parentId !== null || $hierarchyType !== null || $postalCode !== null) {
            return;
        }

        $this->container->make(ResolutionGapReporter::class)->report(
            'search',
            $countryCode,
            $role ?? 'area',
            $query,
        );
    }

    /** @return list<string> */
    private function postalCodeLookupKeys(string $postalCode, ?string $countryCode): array
    {
        $postalCode = mb_trim($postalCode);

        if ($countryCode === null) {
            return [$postalCode];
        }

        $countryCode = mb_strtoupper(mb_trim($countryCode));
        $configured = config('addressing.geography.providers', []);

        if ($this->configuredProviders !== $configured) {
            $this->configuredProviders = is_array($configured) ? $configured : [];
            $this->postalCodeNormalizers = [];
        }

        if (! array_key_exists($countryCode, $this->postalCodeNormalizers)) {
            $this->postalCodeNormalizers[$countryCode] = $this->resolvePostalCodeNormalizer($countryCode);
        }

        $normalizer = $this->postalCodeNormalizers[$countryCode];

        if ($normalizer === null) {
            return [$postalCode];
        }

        return $this->container->make($normalizer)->postalCodeLookupKeys($postalCode);
    }

    /** @return ?class-string<CountryPostalCodeNormalizer> */
    private function resolvePostalCodeNormalizer(string $countryCode): ?string
    {
        foreach (config('addressing.geography.providers', []) as $providerClass) {
            if (! is_string($providerClass)) {
                continue;
            }

            $provider = $this->container->make($providerClass);

            if (! $provider instanceof CountryGeographyProvider
                || mb_strtoupper(mb_trim($provider->countryCode())) !== $countryCode) {
                continue;
            }

            return $provider instanceof CountryPostalCodeNormalizer ? $providerClass : null;
        }

        return null;
    }
}
