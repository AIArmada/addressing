<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Support\CitySeedRowReader;

class SeedAddressingAction
{
    public function __construct(
        private SeedAddressCountriesAction $seedCountries,
        private SeedAddressCountryReferencesAction $seedCountryReferences,
        private SeedAddressStatesAction $seedStates,
        private SeedAddressCitiesAction $seedCities,
        private SeedCountryGeographiesAction $seedGeographies,
        private SeedPostalCodesAction $seedPostalCodes,
    ) {}

    /**
     * Seed the full addressing reference dataset: countries, currency and
     * timezone references, states, cities, every configured country geography,
     * then every bundled postcode dataset. This is the single entry point apps
     * should call.
     *
     * @return array{countries: array<string, mixed>, references: array<string, mixed>, states: array<string, mixed>, cities: array<string, mixed>, geographies: array<string, mixed>, postal_codes: array{seeded: list<string>, skipped: list<string>, codes: array<string, array{created: int, updated: int, skipped: int, links: int}>}}
     */
    public function execute(?callable $progress = null): array
    {
        $countries = $this->seedCountries->execute();
        $references = $this->seedCountryReferences->execute();
        $states = $this->seedStates->execute();
        $cities = $this->seedCities->execute($this->citySeedRows());
        $geographies = $this->seedGeographies->execute(null, $progress);
        $postalCodes = config('addressing.seed.postal_codes', true)
            ? $this->seedPostalCodes->execute(null, $progress)
            : ['seeded' => [], 'skipped' => [], 'codes' => []];

        return [
            'countries' => $countries,
            'references' => $references,
            'states' => $states,
            'cities' => $cities,
            'geographies' => $geographies,
            'postal_codes' => $postalCodes,
        ];
    }

    /**
     * Filtered city rows for non-production, or null for the full dataset.
     *
     * @return list<array<string, mixed>>|null
     */
    private function citySeedRows(): ?array
    {
        return (new CitySeedRowReader(__DIR__ . '/../../resources/data/cities.json'))->rowsForSeed(
            config('addressing.seed.full_city_countries', []),
            app()->isProduction(),
        );
    }
}
