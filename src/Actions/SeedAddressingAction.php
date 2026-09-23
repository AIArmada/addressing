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
    ) {}

    /**
     * Seed the full addressing reference dataset: countries, currency and
     * timezone references, states, cities, then every configured country
     * geography. This is the single entry point apps should call.
     *
     * @return array{countries: array<string, mixed>, references: array<string, mixed>, states: array<string, mixed>, cities: array<string, mixed>, geographies: array<string, mixed>}
     */
    public function execute(?callable $progress = null): array
    {
        $countries = $this->seedCountries->execute();
        $references = $this->seedCountryReferences->execute();
        $states = $this->seedStates->execute();
        $cities = $this->seedCities->execute($this->citySeedRows());
        $geographies = $this->seedGeographies->execute(null, $progress);

        return [
            'countries' => $countries,
            'references' => $references,
            'states' => $states,
            'cities' => $cities,
            'geographies' => $geographies,
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
