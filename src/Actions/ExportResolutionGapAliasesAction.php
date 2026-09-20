<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\ExportResolutionGapAliasesResultData;
use AIArmada\Addressing\Models\AddressArea;
use AIArmada\Addressing\Models\AddressAreaName;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Models\ResolutionGap;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class ExportResolutionGapAliasesAction
{
    public function __construct(
        private readonly Container $container,
    ) {}

    public function execute(string $countryCode, bool $prune = false): ExportResolutionGapAliasesResultData
    {
        $countryCode = mb_strtoupper(mb_trim($countryCode));
        $providers = $this->providersForCountry($countryCode);

        if ($providers === []) {
            throw new InvalidArgumentException(sprintf(
                'No geography provider is configured for country [%s].',
                $countryCode,
            ));
        }

        $country = AddressCountry::query()->where('iso2', $countryCode)->first();

        if (! $country instanceof AddressCountry) {
            throw new InvalidArgumentException(sprintf(
                'Cannot export gap aliases for %s because the country has not been seeded.',
                $countryCode,
            ));
        }

        $warnings = [];
        /** @var array<string, array{shipped: array<string, true>, declared: array<string, array<string, true>>}> $providerData */
        $providerData = [];

        foreach ($providers as $providerClass => $provider) {
            if (! $provider instanceof CountryAddressAreaMetadataProvider) {
                $warnings[] = sprintf('%s does not provide area names and was skipped.', $providerClass);

                continue;
            }

            $shipped = [];

            if ($provider instanceof CountryHierarchyProvider) {
                foreach ($provider->addressAreaSource()->areas() as $area) {
                    $shipped[$area->sourceId] = true;
                }
            }

            $declared = [];

            foreach ($provider->areaNames($country) as $sourceId => $names) {
                foreach ($names as $name) {
                    $declared[(string) $sourceId][LogAddressResolutionGapAction::normalize((string) ($name['name'] ?? ''))] = true;
                }
            }

            $providerData[$providerClass] = ['shipped' => $shipped, 'declared' => $declared];
        }

        /** @var array<string, list<array{source_id: string, name: string, name_type: string, is_preferred: bool}>> $candidates */
        $candidates = [];
        $skippedUnshipped = 0;
        $skippedDeclared = 0;
        $skippedMissingAlias = 0;
        $skippedMissingArea = 0;
        $skippedDuplicates = 0;
        $emitted = [];

        $gaps = ResolutionGap::query()
            ->where('country_code', $countryCode)
            ->where('status', 'matched')
            ->whereNotNull('matched_area_id')
            ->with('matchedArea')
            ->get();

        $manualAliases = AddressAreaName::query()
            ->whereIn('address_area_id', $gaps->map(fn (ResolutionGap $gap): mixed => $gap->matched_area_id)->unique()->all())
            ->where('source', 'manual')
            ->get()
            ->groupBy(fn (AddressAreaName $alias): string => (string) $alias->address_area_id);

        foreach ($gaps as $gap) {
            $area = $gap->matchedArea;

            if (! $area instanceof AddressArea) {
                $skippedMissingArea++;

                continue;
            }

            $providerClass = $this->shippingProvider($providerData, (string) $area->source_id);

            if ($providerClass === null) {
                $skippedUnshipped++;
                $warnings[] = sprintf(
                    'Skipped alias for area [%s] (%s): the area is not shipped by any %s provider, so the alias has nowhere to land.',
                    (string) $area->source_id,
                    (string) $area->name,
                    $countryCode,
                );

                continue;
            }

            $normalized = (string) $gap->normalized;
            $alias = $manualAliases
                ->get((string) $area->getKey(), collect())
                ->first(fn (AddressAreaName $candidate): bool => mb_strtolower((string) $candidate->name) === $normalized);

            if (! $alias instanceof AddressAreaName) {
                $skippedMissingAlias++;

                continue;
            }

            $sourceId = (string) $area->source_id;

            if (isset($providerData[$providerClass]['declared'][$sourceId][$normalized])) {
                $skippedDeclared++;

                continue;
            }

            $dedupeKey = $sourceId . "\0" . mb_strtolower((string) $alias->name);

            if (isset($emitted[$dedupeKey])) {
                $skippedDuplicates++;

                continue;
            }

            $emitted[$dedupeKey] = true;
            $candidates[$providerClass][] = [
                'source_id' => $sourceId,
                'name' => (string) $alias->name,
                'name_type' => (string) $alias->name_type,
                'is_preferred' => (bool) $alias->is_preferred,
            ];
        }

        foreach ($candidates as $providerClass => $rows) {
            usort($rows, static fn (array $a, array $b): int => [$a['source_id'], $a['name']] <=> [$b['source_id'], $b['name']]);
            $candidates[$providerClass] = $rows;
        }

        ksort($candidates);

        $pruned = $prune ? $this->pruneDuplicatedManualAliases($countryCode) : 0;

        return new ExportResolutionGapAliasesResultData(
            countryCode: $countryCode,
            candidatesByProvider: $candidates,
            skippedUnshipped: $skippedUnshipped,
            skippedDeclared: $skippedDeclared,
            skippedMissingAlias: $skippedMissingAlias,
            skippedMissingArea: $skippedMissingArea,
            skippedDuplicates: $skippedDuplicates,
            pruned: $pruned,
            warnings: $warnings,
        );
    }

    /**
     * @return array<string, CountryGeographyProvider>
     */
    private function providersForCountry(string $countryCode): array
    {
        $providers = [];

        foreach (config('addressing.geography.providers', []) as $providerClass) {
            if (! is_string($providerClass)) {
                throw new InvalidArgumentException('Addressing geography providers must be class strings.');
            }

            $provider = $this->container->make($providerClass);

            if (! $provider instanceof CountryGeographyProvider) {
                throw new InvalidArgumentException(sprintf(
                    '%s must implement %s.',
                    $providerClass,
                    CountryGeographyProvider::class,
                ));
            }

            if (mb_strtoupper(mb_trim($provider->countryCode())) === $countryCode) {
                $providers[$providerClass] = $provider;
            }
        }

        return $providers;
    }

    /**
     * @param  array<string, array{shipped: array<string, true>, declared: array<string, array<string, true>>}>  $providerData
     */
    private function shippingProvider(array $providerData, string $sourceId): ?string
    {
        foreach ($providerData as $providerClass => $data) {
            if (isset($data['shipped'][$sourceId])) {
                return $providerClass;
            }
        }

        return null;
    }

    private function pruneDuplicatedManualAliases(string $countryCode): int
    {
        return DB::transaction(function () use ($countryCode): int {
            $areaIds = AddressArea::query()->where('country_code', $countryCode)->pluck('id')->all();

            if ($areaIds === []) {
                return 0;
            }

            $providerPairs = AddressAreaName::query()
                ->whereIn('address_area_id', $areaIds)
                ->where('source', '!=', 'manual')
                ->get(['address_area_id', 'name'])
                ->mapToGroups(static fn (AddressAreaName $name): array => [(string) $name->address_area_id => (string) $name->name])
                ->map(static fn (mixed $names): array => collect($names)->all())
                ->all();

            $manualAliases = AddressAreaName::query()
                ->whereIn('address_area_id', $areaIds)
                ->where('source', 'manual')
                ->get(['id', 'address_area_id', 'name']);

            $duplicates = [];

            foreach ($manualAliases as $alias) {
                $areaId = (string) $alias->address_area_id;

                if (in_array((string) $alias->name, $providerPairs[$areaId] ?? [], true)) {
                    $duplicates[] = $alias->getKey();
                }
            }

            if ($duplicates === []) {
                return 0;
            }

            return AddressAreaName::query()->whereIn('id', $duplicates)->delete();
        });
    }
}
