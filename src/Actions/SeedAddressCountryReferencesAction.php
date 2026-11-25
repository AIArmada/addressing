<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Support\AddressingTableResolver;
use AIArmada\Addressing\Support\ModelResolver;
use AIArmada\CommerceSupport\Features\ReferenceData\Api\ReferenceDataApi;
use Carbon\CarbonImmutable;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Str;
use RuntimeException;

class SeedAddressCountryReferencesAction
{
    public function __construct(
        private readonly ReferenceDataApi $referenceData,
    ) {}

    /**
     * @param  ?callable(string, int, int, ?string=): void  $progress
     * @return array{currency_links: int, timezone_links: int}
     */
    public function execute(?callable $progress = null): array
    {
        $this->referenceData->seedCurrencies();
        $this->referenceData->seedTimezones();

        $countries = json_decode(
            file_get_contents(__DIR__ . '/../../resources/data/countries.json'),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        if (! is_array($countries)) {
            throw new RuntimeException('Country data file must contain a JSON array.');
        }

        $total = count($countries) * 2;

        $result = $this->connection()->transaction(function () use ($countries, $progress, $total): array {
            $this->clearExistingLinks($countries);

            return [
                'currency_links' => $this->syncCurrencies($countries, $progress, $total, 0),
                'timezone_links' => $this->syncTimezones($countries, $progress, $total, count($countries)),
            ];
        });

        if ($progress !== null && $total === 0) {
            $progress('references', 0, 0);
        }

        return $result;
    }

    /**
     * @param  array<int, mixed>  $countries
     */
    private function clearExistingLinks(array $countries): void
    {
        $countryCodes = collect($countries)
            ->filter(static fn (mixed $country): bool => is_array($country))
            ->pluck('iso2')
            ->filter(static fn (mixed $code): bool => is_string($code))
            ->values();

        if ($countryCodes->isEmpty()) {
            return;
        }

        $countryIds = ModelResolver::countryClass()::query()
            ->whereIn('iso2', $countryCodes)
            ->pluck('id');

        if ($countryIds->isEmpty()) {
            return;
        }

        foreach (['country_currency_links', 'country_timezone_links'] as $configKey) {
            $this->connection()->table(AddressingTableResolver::resolve($configKey))
                ->whereIn('country_id', $countryIds)
                ->delete();
        }
    }

    /**
     * @param  array<int, mixed>  $countries
     * @param  ?callable(string, int, int, ?string=): void  $progress
     */
    private function syncCurrencies(array $countries, ?callable $progress = null, int $total = 0, int $offset = 0): int
    {
        $currencyIds = $this->referenceData->currencyIdsByCode();
        $countryIds = ModelResolver::countryClass()::query()->pluck('id', 'iso2');
        $links = [];

        foreach ($countries as $index => $country) {
            if ($progress !== null) {
                $progress('references', $offset + $index + 1, $total);
            }

            if (! is_array($country)) {
                continue;
            }

            $countryId = $countryIds->get($country['iso2'] ?? null);
            $currencyId = $currencyIds[$country['currency'] ?? ''] ?? null;

            if ($countryId === null || $currencyId === null) {
                continue;
            }

            $links[] = ['country_id' => $countryId, 'currency_id' => $currencyId];
        }

        return $this->syncLinks(
            AddressingTableResolver::resolve('country_currency_links'),
            $links,
            'currency_id',
        );
    }

    /**
     * @param  array<int, mixed>  $countries
     * @param  ?callable(string, int, int, ?string=): void  $progress
     */
    private function syncTimezones(array $countries, ?callable $progress = null, int $total = 0, int $offset = 0): int
    {
        $timezoneIds = $this->referenceData->timezoneIdsByName();
        $countryIds = ModelResolver::countryClass()::query()->pluck('id', 'iso2');
        $links = [];

        foreach ($countries as $index => $country) {
            if ($progress !== null) {
                $progress('references', $offset + $index + 1, $total);
            }

            if (! is_array($country)) {
                continue;
            }

            $countryId = $countryIds->get($country['iso2'] ?? null);

            if ($countryId === null) {
                continue;
            }

            foreach ($country['timezones'] ?? [] as $timezone) {
                $name = is_array($timezone) ? ($timezone['zoneName'] ?? null) : $timezone;
                $timezoneId = $timezoneIds[$name ?? ''] ?? null;

                if ($timezoneId === null) {
                    continue;
                }

                $links[] = ['country_id' => $countryId, 'timezone_id' => $timezoneId];
            }
        }

        return $this->syncLinks(
            AddressingTableResolver::resolve('country_timezone_links'),
            $links,
            'timezone_id',
        );
    }

    /**
     * @param  list<array<string, string>>  $links
     */
    private function syncLinks(string $table, array $links, string $relatedColumn): int
    {
        if ($links === []) {
            return 0;
        }
        $now = CarbonImmutable::now();
        $created = 0;

        foreach (array_chunk($links, 1000) as $chunk) {
            $rows = array_map(static fn (array $link): array => [
                'id' => (string) Str::uuid(),
                ...$link,
                'created_at' => $now,
                'updated_at' => $now,
            ], $chunk);

            $created += $this->connection()->table($table)->insertOrIgnore($rows);
        }

        return $created;
    }

    private function connection(): ConnectionInterface
    {
        return ModelResolver::countryClass()::query()->getConnection();
    }
}
