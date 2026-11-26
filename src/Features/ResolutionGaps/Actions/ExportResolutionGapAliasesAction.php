<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\ResolutionGaps\Actions;

use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\ExportResolutionGapAliasesResultData;
use AIArmada\Addressing\Features\Geography\Api\AreaReferenceReader;
use AIArmada\Addressing\Features\Geography\Api\ResolutionAliasMaintenance;
use AIArmada\Addressing\Features\ResolutionGaps\Models\ResolutionGap;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

final readonly class ExportResolutionGapAliasesAction
{
    public function __construct(
        private Container $container,
        private ResolutionAliasMaintenance $aliases,
        private AreaReferenceReader $references,
    ) {}

    /**
     * Runs inside the caller's owner scope by default. Pass $allOwners from
     * privileged CLI curation to consolidate matched gaps across every owner.
     */
    public function execute(string $countryCode, bool $prune = false, bool $allOwners = false): ExportResolutionGapAliasesResultData
    {
        $countryCode = mb_strtoupper(mb_trim($countryCode));
        $providers = $this->providersForCountry($countryCode);

        if ($providers === []) {
            throw new InvalidArgumentException(sprintf(
                'No geography provider is configured for country [%s].',
                $countryCode,
            ));
        }

        if (! $this->references->countryExists($countryCode)) {
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

            foreach ($provider->areaNames() as $sourceId => $names) {
                foreach ($names as $name) {
                    $declared[(string) $sourceId][ReportResolutionGapAction::normalize((string) ($name['name'] ?? ''))] = true;
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

        $gaps = ResolutionGap::configuredClass()::query()
            ->when($allOwners, fn ($query) => $query->withoutOwnerScope())
            ->where('country_code', $countryCode)
            ->where('status', 'matched')
            ->whereNotNull('matched_area_id')
            ->get();

        $areaIds = $gaps->map(fn (ResolutionGap $gap): string => (string) $gap->matched_area_id)->unique()->values()->all();
        $areas = $this->references->areaMetadata($areaIds);
        $manualAliases = collect($this->references->aliases(
            $areaIds,
            'manual',
        ))->groupBy('area_id');

        foreach ($gaps as $gap) {
            $area = $areas[(string) $gap->matched_area_id] ?? null;

            if ($area === null) {
                $skippedMissingArea++;

                continue;
            }

            $providerClass = $this->shippingProvider($providerData, $area['source_id']);

            if ($providerClass === null) {
                $skippedUnshipped++;
                $warnings[] = sprintf(
                    'Skipped alias for area [%s] (%s): the area is not shipped by any %s provider, so the alias has nowhere to land.',
                    $area['source_id'],
                    $area['name'],
                    $countryCode,
                );

                continue;
            }

            $normalized = (string) $gap->normalized;
            $alias = $manualAliases
                ->get((string) $gap->matched_area_id, collect())
                ->first(fn (array $candidate): bool => mb_strtolower($candidate['name']) === $normalized);

            if ($alias === null) {
                $skippedMissingAlias++;

                continue;
            }

            $sourceId = $area['source_id'];

            if (isset($providerData[$providerClass]['declared'][$sourceId][$normalized])) {
                $skippedDeclared++;

                continue;
            }

            $dedupeKey = $sourceId . "\0" . mb_strtolower($alias['name']);

            if (isset($emitted[$dedupeKey])) {
                $skippedDuplicates++;

                continue;
            }

            $emitted[$dedupeKey] = true;
            $candidates[$providerClass][] = [
                'source_id' => $sourceId,
                'name' => $alias['name'],
                'name_type' => $alias['name_type'],
                'is_preferred' => $alias['is_preferred'],
            ];
        }

        foreach ($candidates as $providerClass => $rows) {
            usort($rows, static fn (array $a, array $b): int => [$a['source_id'], $a['name']] <=> [$b['source_id'], $b['name']]);
            $candidates[$providerClass] = $rows;
        }

        ksort($candidates);

        $pruned = $prune ? $this->aliases->pruneDuplicates($countryCode) : 0;

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
}
