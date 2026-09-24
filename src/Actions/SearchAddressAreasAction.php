<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryPostalCodeNormalizer;
use AIArmada\Addressing\Models\AddressArea;
use AIArmada\CommerceSupport\Support\LikeSearch;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class SearchAddressAreasAction
{
    /** @var array<string, ?class-string> */
    private array $postalCodeNormalizers = [];

    public function __construct(private readonly Container $container) {}

    /**
     * @return Collection<int, AddressArea>
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
    ): Collection {
        $query = mb_trim($query);

        if ($query === '') {
            return new Collection;
        }

        $needle = mb_strtolower($query);
        $escapedNeedle = LikeSearch::escape($needle);
        $containsPattern = "%{$escapedNeedle}%";
        $prefixPattern = "{$escapedNeedle}%";

        if ($hierarchyType !== null && mb_trim($hierarchyType) === '') {
            return new Collection;
        }

        $escapeClause = LikeSearch::escapeClause(AddressArea::query()->getConnection());

        return AddressArea::query()
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
            ->limit(max(1, min($limit, 100)))
            ->get();
    }

    /** @return list<string> */
    private function postalCodeLookupKeys(string $postalCode, ?string $countryCode): array
    {
        $postalCode = mb_trim($postalCode);

        if ($countryCode === null) {
            return [$postalCode];
        }

        $countryCode = mb_strtoupper(mb_trim($countryCode));

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
