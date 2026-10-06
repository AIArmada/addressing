<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Data\PostalCodeData;
use AIArmada\Addressing\Models\AddressArea;
use AIArmada\Addressing\Models\AddressAreaPostalCode;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Models\PostalCode;
use AIArmada\Addressing\Support\BundledPostalDatasets;
use AIArmada\Addressing\Support\CsvPostalCodeSource;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class SeedPostalCodesAction
{
    private const int CHUNK_SIZE = 500;

    private const int FAILURE_SAMPLE = 5;

    public function __construct(
        private readonly Container $container,
    ) {}

    /**
     * Seed every bundled postcode dataset: postal codes plus their area links.
     *
     * Countries without a bundled `*-postal-codes.csv` pair are skipped: either
     * they have no national postcode system, or they resolve postcodes at
     * runtime (Singapore via OneMap). Seed countries and country geographies
     * first; links resolve against the seeded areas. The `links` count reports
     * link rows written, so a no-op reseed reports zero.
     *
     * @param  ?callable(string, int, int, ?string=): void  $progress
     * @return array{seeded: list<string>, skipped: list<string>, codes: array<string, array{created: int, updated: int, skipped: int, links: int}>}
     */
    public function execute(?string $countryCode = null, ?callable $progress = null): array
    {
        $requestedCode = $countryCode !== null ? mb_strtoupper(mb_trim($countryCode)) : null;
        $providers = $this->providersByCountry();
        $seeded = [];
        $skipped = [];
        $codes = [];

        foreach (BundledPostalDatasets::datasets() as [$code, $codesPath, $linksPath]) {
            if ($requestedCode !== null && $requestedCode !== $code) {
                $skipped[] = $code;

                continue;
            }

            $provider = $providers[$code] ?? null;

            if (! $provider instanceof CountryGeographyProvider || ! $provider instanceof CountryHierarchyProvider) {
                $skipped[] = $code;

                continue;
            }

            $country = AddressCountry::query()->where('iso2', $code)->first();

            if (! $country instanceof AddressCountry) {
                throw new InvalidArgumentException(sprintf(
                    'Cannot seed postcodes for %s because the country has not been seeded.',
                    $code,
                ));
            }

            $report = $progress === null
                ? null
                : function (string $phase, int $done, int $total) use ($progress, $code): void {
                    $progress($phase, $done, $total, "{$code} {$phase}");
                };

            $areaSource = $provider->addressAreaSource()->key();
            $source = new CsvPostalCodeSource($code, $codesPath, $linksPath, $areaSource);

            $codes[$code] = DB::transaction(fn (): array => $this->importCountry($code, $areaSource, $source, $report));
            $seeded[] = $code;
        }

        return [
            'seeded' => array_values(array_unique($seeded)),
            'skipped' => array_values(array_unique($skipped)),
            'codes' => $codes,
        ];
    }

    /**
     * @return array{created: int, updated: int, skipped: int, links: int}
     */
    private function importCountry(string $code, string $areaSource, CsvPostalCodeSource $source, ?callable $progress): array
    {
        $wantedCodes = [];
        $wantedLinks = [];

        foreach ($source->postalCodes() as $item) {
            $upper = mb_strtoupper(mb_trim($item->code));

            if ($upper === '') {
                continue;
            }

            $wantedCodes[$upper] = $item;

            if ($item->areaSourceId !== null) {
                $wantedLinks[] = $item;
            }
        }

        $areaIds = AddressArea::query()
            ->where('country_code', $code)
            ->where('source', $areaSource)
            ->pluck('id', 'source_id')
            ->all();

        $failures = [];

        foreach ($wantedLinks as $item) {
            if (! isset($areaIds[$item->areaSourceId ?? ''])) {
                $failures[] = "[{$item->code}] area not found: {$item->areaSourceId}";
            }
        }

        if ($failures !== []) {
            throw new InvalidArgumentException(sprintf(
                'Cannot seed %s postcodes because %d area links failed (%s). Seed country geographies first.',
                $code,
                count($failures),
                implode('; ', array_slice($failures, 0, self::FAILURE_SAMPLE)),
            ));
        }

        $now = CarbonImmutable::now()->toDateTimeString();
        $counts = $this->syncCodes($code, $wantedCodes, $now, $progress);
        $counts['links'] = $this->syncLinks($code, $source->key(), $wantedLinks, $areaIds, $now, $progress);

        return $counts;
    }

    /**
     * @param  array<string, PostalCodeData>  $wantedCodes
     * @return array{created: int, updated: int, skipped: int}
     */
    private function syncCodes(string $code, array $wantedCodes, string $now, ?callable $progress): array
    {
        $existing = PostalCode::query()
            ->where('country_code', $code)
            ->get(['code', 'is_active', 'metadata'])
            ->keyBy('code');

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $upserts = [];

        foreach ($wantedCodes as $upper => $item) {
            $metadata = array_merge($item->metadata, [
                'source' => $item->source,
                'source_id' => $item->sourceId,
            ]);
            $row = $existing->get($upper);

            if ($row === null) {
                $created++;
            } elseif ($row->is_active !== true || $row->metadata !== $metadata) {
                $updated++;
            } else {
                $skipped++;

                continue;
            }

            $upserts[] = [
                'id' => (string) Str::uuid7(),
                'country_code' => $code,
                'code' => $upper,
                'is_active' => true,
                'metadata' => json_encode($metadata, JSON_THROW_ON_ERROR),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $total = count($upserts);

        foreach (array_chunk($upserts, self::CHUNK_SIZE) as $index => $chunk) {
            PostalCode::query()->upsert($chunk, ['country_code', 'code'], ['is_active', 'metadata', 'updated_at']);

            if ($progress !== null) {
                $progress('codes', min(($index + 1) * self::CHUNK_SIZE, $total), $total);
            }
        }

        return ['created' => $created, 'updated' => $updated, 'skipped' => $skipped];
    }

    /**
     * @param  list<PostalCodeData>  $wantedLinks
     * @param  array<string, string>  $areaIds
     */
    private function syncLinks(string $code, string $linkSource, array $wantedLinks, array $areaIds, string $now, ?callable $progress): int
    {
        $postalIds = PostalCode::query()
            ->where('country_code', $code)
            ->pluck('id', 'code')
            ->all();

        $desired = [];

        foreach ($wantedLinks as $item) {
            $upper = mb_strtoupper(mb_trim($item->code));

            $desired[$postalIds[$upper] . '|' . $areaIds[$item->areaSourceId ?? ''] . '|' . $item->relationshipType] = [
                'postal_code_id' => $postalIds[$upper],
                'address_area_id' => $areaIds[$item->areaSourceId ?? ''],
                'relationship_type' => $item->relationshipType,
                'source_id' => $item->sourceId,
                'is_primary' => $item->isPrimary,
            ];
        }

        $stale = [];
        $upserts = [];

        $existingLinks = AddressAreaPostalCode::query()
            ->where('source', $linkSource)
            ->whereHas('postalCode', fn ($query) => $query->where('country_code', $code))
            ->get(['id', 'address_area_id', 'postal_code_id', 'relationship_type', 'source_id', 'is_primary']);

        foreach ($existingLinks as $link) {
            $key = $link->postal_code_id . '|' . $link->address_area_id . '|' . $link->relationship_type;
            $want = $desired[$key] ?? null;

            if ($want === null) {
                $stale[] = $link->getKey();

                continue;
            }

            if ($link->source_id !== $want['source_id'] || (bool) $link->is_primary !== $want['is_primary']) {
                $upserts[] = self::linkRow($want, $linkSource, $now);
            }

            unset($desired[$key]);
        }

        foreach ($desired as $want) {
            $upserts[] = self::linkRow($want, $linkSource, $now);
        }

        foreach (array_chunk($stale, self::CHUNK_SIZE) as $chunk) {
            AddressAreaPostalCode::query()->whereIn('id', $chunk)->delete();
        }

        $total = count($upserts);

        foreach (array_chunk($upserts, self::CHUNK_SIZE) as $index => $chunk) {
            AddressAreaPostalCode::query()->upsert(
                $chunk,
                ['address_area_id', 'postal_code_id', 'relationship_type', 'source'],
                ['source_id', 'is_primary', 'updated_at'],
            );

            if ($progress !== null) {
                $progress('links', min(($index + 1) * self::CHUNK_SIZE, $total), $total);
            }
        }

        return $total;
    }

    /**
     * @param  array{postal_code_id: string, address_area_id: string, relationship_type: string, source_id: string, is_primary: bool}  $want
     * @return array{postal_code_id: string, address_area_id: string, relationship_type: string, source_id: string, is_primary: bool, id: string, source: string, created_at: string, updated_at: string}
     */
    private static function linkRow(array $want, string $linkSource, string $now): array
    {
        return $want + [
            'id' => (string) Str::uuid7(),
            'source' => $linkSource,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * @return array<string, CountryGeographyProvider>
     */
    private function providersByCountry(): array
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

            $providers[mb_strtoupper(mb_trim($provider->countryCode()))] = $provider;
        }

        return $providers;
    }
}
