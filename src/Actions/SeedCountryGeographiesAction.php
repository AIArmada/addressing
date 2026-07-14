<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;
use AIArmada\Addressing\Models\AddressArea;
use AIArmada\Addressing\Models\AddressAreaStateLink;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\ModelResolver;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class SeedCountryGeographiesAction
{
    public function __construct(
        private readonly Container $container,
        private readonly ImportAddressAreasAction $importAddressAreas,
    ) {}

    /**
     * @return array{seeded: list<string>, skipped: list<string>, areas: array}
     */
    public function execute(?string $countryCode = null): array
    {
        $seeded = [];
        $skipped = [];
        $areas = [];
        $requestedCode = $countryCode !== null ? mb_strtoupper(mb_trim($countryCode)) : null;

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

            $providerCode = mb_strtoupper(mb_trim($provider->countryCode()));

            if ($requestedCode !== null && $requestedCode !== $providerCode) {
                $skipped[] = $providerCode;

                continue;
            }

            $country = AddressCountry::query()->where('iso2', $providerCode)->first();

            if (! $country instanceof AddressCountry) {
                throw new InvalidArgumentException(sprintf(
                    'Cannot seed geography for %s because the country has not been seeded.',
                    $providerCode,
                ));
            }

            $provider->seed($country);

            if ($provider instanceof CountryHierarchyProvider) {
                $areaResult = $this->importAddressAreas->execute($provider->addressAreaSource());

                if ($areaResult->hasFailures()) {
                    throw new InvalidArgumentException(sprintf(
                        'Cannot seed %s address hierarchy because %d area rows failed.',
                        $providerCode,
                        count($areaResult->failures),
                    ));
                }

                $this->linkStateAreas($country, $provider->stateAreaMappings());
                $areas[$providerCode] = [
                    'created' => $areaResult->created,
                    'updated' => $areaResult->updated,
                    'skipped' => $areaResult->skipped,
                ];
            }

            $seeded[] = $providerCode;
        }

        return [
            'seeded' => array_values(array_unique($seeded)),
            'skipped' => array_values(array_unique($skipped)),
            'areas' => $areas,
        ];
    }

    /**
     * @param  array<string, array{area_code: string, source: string, area_level: int}>  $mappings
     */
    private function linkStateAreas(AddressCountry $country, array $mappings): void
    {
        $stateClass = ModelResolver::stateClass();

        DB::transaction(function () use ($country, $mappings, $stateClass): void {
            foreach ($mappings as $stateCode => $mapping) {
                $state = $stateClass::query()
                    ->where('country_id', $country->getKey())
                    ->where('code', $stateCode)
                    ->firstOrFail();

                $area = AddressArea::query()
                    ->where('country_id', $country->getKey())
                    ->where('source', $mapping['source'])
                    ->where('level', $mapping['area_level'])
                    ->where('code', $mapping['area_code'])
                    ->firstOrFail();

                AddressAreaStateLink::query()->updateOrCreate(
                    [
                        'address_area_id' => $area->getKey(),
                        'state_id' => $state->getKey(),
                    ],
                    ['metadata' => ['provider' => $country->iso2]],
                );
            }
        });
    }
}
