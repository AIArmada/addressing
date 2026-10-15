<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Support\AddressingReferenceRecords;
use AIArmada\Addressing\Support\AddressingTableResolver;
use AIArmada\Addressing\Support\ReferenceExportContract;
use AIArmada\Addressing\Support\ReferenceExportSorter;
use Illuminate\Database\Connection;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class ExportAddressingReferencesAction
{
    public function __construct(
        private readonly AddressingReferenceRecords $records,
        private readonly ReferenceExportSorter $sorter,
    ) {}

    /**
     * Export only global geography and its explicitly allowlisted relationships.
     *
     * @param  list<string>  $countryCodes  Empty means every installed country.
     * @return array{path: string, countries: list<string>, counts: array<string, int>, sha256: string}
     */
    public function execute(string $output, array $countryCodes = [], ?string $seedRevision = null): array
    {
        $countryCodes = $this->normalizeCountries($countryCodes);
        $seedRevision = $seedRevision === null ? null : mb_trim($seedRevision);

        if ($seedRevision === '') {
            throw new InvalidArgumentException('Seed revision must be a factual non-empty revision or omitted.');
        }

        $directory = dirname($output);

        if (! is_dir($directory) || ! is_writable($directory)) {
            throw new InvalidArgumentException(sprintf('Reference export directory [%s] must exist and be writable.', $directory));
        }

        $connection = $this->records->connection();

        if ($connection->getDriverName() === 'pgsql' && $connection->transactionLevel() > 0) {
            throw new InvalidArgumentException('PostgreSQL reference exports cannot run inside an existing transaction because the snapshot boundary must be established before any queries.');
        }

        $temporaryPath = tempnam($directory, '.addressing-reference-export-');

        if ($temporaryPath === false) {
            throw new RuntimeException('Could not create a temporary reference export file.');
        }

        chmod($temporaryPath, 0600);
        $stream = fopen($temporaryPath, 'wb');

        if ($stream === false) {
            @unlink($temporaryPath);

            throw new RuntimeException('Could not open the temporary reference export file.');
        }

        try {
            $result = $this->writeSnapshot($connection, $stream, $output, $directory, $countryCodes, $seedRevision);
            fclose($stream);
            $stream = null;

            if (! rename($temporaryPath, $output)) {
                throw new RuntimeException(sprintf('Could not atomically publish reference export [%s].', $output));
            }

            chmod($output, 0600);

            return ['path' => $output, ...$result];
        } catch (Throwable $exception) {
            if (is_resource($stream)) {
                fclose($stream);
            }

            @unlink($temporaryPath);

            throw $exception;
        }
    }

    /**
     * @param  resource  $stream
     * @param  list<string>  $requestedCountries
     * @return array{countries: list<string>, counts: array<string, int>, sha256: string}
     */
    private function writeSnapshot(Connection $connection, mixed $stream, string $output, string $directory, array $requestedCountries, ?string $seedRevision): array
    {
        return $connection->transaction(function () use ($connection, $stream, $directory, $requestedCountries, $seedRevision): array {
            if ($connection->getDriverName() === 'pgsql') {
                $connection->statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ READ ONLY');
            }

            $countryQuery = $connection->table(AddressingTableResolver::resolve('countries'))->select('iso2')->orderBy('iso2');

            if ($requestedCountries !== []) {
                $countryQuery->whereIn('iso2', $requestedCountries);
            }

            $countries = $countryQuery->pluck('iso2')->map(static fn (mixed $code): string => (string) $code)->all();

            if ($requestedCountries !== [] && count($countries) !== count($requestedCountries)) {
                $missing = array_values(array_diff($requestedCountries, $countries));

                throw new InvalidArgumentException(sprintf('Unknown addressing country code(s): %s.', implode(', ', $missing)));
            }

            $header = [
                'kind' => 'header',
                'schema' => ReferenceExportContract::SCHEMA,
                'version' => ReferenceExportContract::VERSION,
                'exported_at' => now()->toImmutable()->utc()->format('Y-m-d\TH:i:s.u\Z'),
                'seed_revision' => $seedRevision,
                'coverage' => 'installed-observation-only',
                'scope' => [
                    'countries' => $countries,
                    'configured_providers' => $this->providerScopes($countries),
                    'seed_policy' => $this->seedPolicy(),
                ],
            ];
            $this->writeLine($stream, ReferenceExportContract::encode($header));

            $counts = array_fill_keys(ReferenceExportContract::TYPES, 0);
            $recordsDigest = hash_init('sha256');
            $lastSortKey = null;

            foreach ($this->sorter->sort($this->records->records($countries), $directory) as $record) {
                $type = $record['type'];
                $sortKey = ReferenceExportContract::sortKey($type, $record['key']);

                if ($lastSortKey !== null && strcmp($sortKey, $lastSortKey) === 0) {
                    throw new InvalidArgumentException(sprintf('Ambiguous %s reference identity prevents a complete export.', $type));
                }

                $lastSortKey = $sortKey;
                $line = ReferenceExportContract::encode($record);
                $this->writeLine($stream, $line);
                $counts[$type]++;
                hash_update($recordsDigest, $line . "\n");
            }

            $digest = hash_final($recordsDigest);
            $this->writeLine($stream, ReferenceExportContract::encode([
                'kind' => 'footer',
                'counts' => $counts,
                'records_sha256' => $digest,
            ]));

            return ['countries' => $countries, 'counts' => $counts, 'sha256' => $digest];
        });
    }

    /** @param list<string> $countries @return list<array{country: string, provider: string}> */
    private function providerScopes(array $countries): array
    {
        $scopes = [];

        foreach (config('addressing.geography.providers', []) as $providerClass) {
            $provider = app($providerClass);

            if (! $provider instanceof CountryGeographyProvider) {
                throw new InvalidArgumentException(sprintf('Configured provider [%s] does not implement CountryGeographyProvider.', $providerClass));
            }

            $country = mb_strtoupper($provider->countryCode());

            if (! in_array($country, $countries, true)) {
                continue;
            }

            $scopes[] = ['country' => $country, 'provider' => $provider->providerKey()];
        }

        usort($scopes, static fn (array $left, array $right): int => [$left['country'], $left['provider']] <=> [$right['country'], $right['provider']]);

        return $scopes;
    }

    /** @return array{full_city_countries: list<string>, production_full_city_seed: bool, postal_codes_enabled: bool, indonesia_villages_enabled: bool} */
    private function seedPolicy(): array
    {
        $fullCityCountries = config('addressing.seed.full_city_countries', []);
        $fullCityCountries = is_array($fullCityCountries) ? array_values(array_filter($fullCityCountries, is_string(...))) : [];
        $fullCityCountries = array_map(mb_strtoupper(...), $fullCityCountries);
        sort($fullCityCountries, SORT_STRING);

        return [
            'full_city_countries' => array_values(array_unique($fullCityCountries)),
            'production_full_city_seed' => app()->isProduction(),
            'postal_codes_enabled' => (bool) config('addressing.seed.postal_codes', true),
            'indonesia_villages_enabled' => (bool) config('addressing.geography.indonesia.villages', false),
        ];
    }

    /** @param list<string> $countryCodes @return list<string> */
    private function normalizeCountries(array $countryCodes): array
    {
        $countries = [];

        foreach ($countryCodes as $countryCode) {
            $normalized = mb_strtoupper(mb_trim($countryCode));

            if (preg_match('/^[A-Z]{2}$/', $normalized) !== 1) {
                throw new InvalidArgumentException(sprintf('Country code [%s] must be a two-letter ISO code.', $countryCode));
            }

            $countries[] = $normalized;
        }

        $countries = array_values(array_unique($countries));
        sort($countries, SORT_STRING);

        return $countries;
    }

    /** @param resource $stream */
    private function writeLine(mixed $stream, string $line): void
    {
        $line .= "\n";

        while ($line !== '') {
            $written = fwrite($stream, $line);

            if ($written === false || $written === 0) {
                throw new RuntimeException('Could not write the reference export.');
            }

            $line = mb_substr($line, $written);
        }
    }
}
