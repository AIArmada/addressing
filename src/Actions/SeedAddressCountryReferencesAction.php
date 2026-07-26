<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\CommerceSupport\Actions\SeedCurrenciesAction;
use AIArmada\CommerceSupport\Actions\SeedTimezonesAction;
use AIArmada\CommerceSupport\Models\Currency;
use AIArmada\CommerceSupport\Models\Timezone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SeedAddressCountryReferencesAction
{
    public function __construct(
        private readonly SeedCurrenciesAction $seedCurrencies,
        private readonly SeedTimezonesAction $seedTimezones,
    ) {}

    /**
     * @return array{currency_links: int, timezone_links: int}
     */
    public function execute(): array
    {
        $this->seedCurrencies->execute();
        $this->seedTimezones->execute();

        $countries = json_decode(
            file_get_contents(__DIR__ . '/../../resources/data/countries.json'),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        if (! is_array($countries)) {
            throw new RuntimeException('Country data file must contain a JSON array.');
        }

        return DB::transaction(function () use ($countries): array {
            $this->clearExistingLinks($countries);

            return [
                'currency_links' => $this->syncCurrencies($countries),
                'timezone_links' => $this->syncTimezones($countries),
            ];
        });
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

        $countryIds = AddressCountry::query()
            ->whereIn('iso2', $countryCodes)
            ->pluck('id');

        if ($countryIds->isEmpty()) {
            return;
        }

        foreach (['country_currency_links', 'country_timezone_links'] as $configKey) {
            DB::table(config("addressing.tables.{$configKey}", $configKey))
                ->whereIn('country_id', $countryIds)
                ->delete();
        }
    }

    /**
     * @param  array<int, mixed>  $countries
     */
    private function syncCurrencies(array $countries): int
    {
        $currencyIds = Currency::query()->pluck('id', 'code');
        $countryIds = AddressCountry::query()->pluck('id', 'iso2');
        $created = 0;

        foreach ($countries as $country) {
            if (! is_array($country)) {
                continue;
            }

            $countryId = $countryIds->get($country['iso2'] ?? null);
            $currencyId = $currencyIds->get($country['currency'] ?? null);

            if ($countryId === null || $currencyId === null) {
                continue;
            }

            $created += $this->insertLink(
                config('addressing.tables.country_currency_links', 'country_currency_links'),
                ['country_id' => $countryId, 'currency_id' => $currencyId],
            );
        }

        return $created;
    }

    /**
     * @param  array<int, mixed>  $countries
     */
    private function syncTimezones(array $countries): int
    {
        $timezoneIds = Timezone::query()->pluck('id', 'name');
        $countryIds = AddressCountry::query()->pluck('id', 'iso2');
        $created = 0;

        foreach ($countries as $country) {
            if (! is_array($country)) {
                continue;
            }

            $countryId = $countryIds->get($country['iso2'] ?? null);

            if ($countryId === null) {
                continue;
            }

            foreach ($country['timezones'] ?? [] as $timezone) {
                $name = is_array($timezone) ? ($timezone['zoneName'] ?? null) : $timezone;
                $timezoneId = $timezoneIds->get($name);

                if ($timezoneId === null) {
                    continue;
                }

                $created += $this->insertLink(
                    config('addressing.tables.country_timezone_links', 'country_timezone_links'),
                    ['country_id' => $countryId, 'timezone_id' => $timezoneId],
                );
            }
        }

        return $created;
    }

    /**
     * @param  array<string, string>  $attributes
     */
    private function insertLink(string $table, array $attributes): int
    {
        return DB::table($table)->insertOrIgnore([
            'id' => (string) Str::uuid(),
            ...$attributes,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
