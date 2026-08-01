<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Contracts\CountryAddressProfile;
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

final class CountryAddressProfileResolver
{
    private const string REQUEST_CACHE_KEY = 'aiarmada.addressing.country-profiles';

    public function __construct(
        private readonly Container $container,
        private readonly AddressCountryResolver $countryResolver,
    ) {}

    public function resolve(mixed $country): ?CountryAddressProfile
    {
        $cacheKey = $this->cacheKey($country);

        if ($cacheKey !== null && app()->bound('request')) {
            $request = request();
            $cache = $request->attributes->get(self::REQUEST_CACHE_KEY, []);

            if (is_array($cache) && array_key_exists($cacheKey, $cache)) {
                $profile = $cache[$cacheKey];

                return $profile instanceof CountryAddressProfile ? $profile : null;
            }
        }

        $resolvedCountry = $this->countryResolver->resolve($country);

        if (! $resolvedCountry instanceof AddressCountry) {
            $this->cache($cacheKey, null);

            return null;
        }

        $countryCode = mb_strtoupper((string) $resolvedCountry->iso2);

        foreach (config('addressing.geography.providers', []) as $providerClass) {
            if (! is_string($providerClass)) {
                throw new InvalidArgumentException('Addressing geography providers must be class strings.');
            }

            $provider = $this->container->make($providerClass);

            if (! $provider instanceof CountryAddressProfile) {
                continue;
            }

            if (mb_strtoupper(mb_trim($provider->countryCode())) === $countryCode) {
                $this->cache($cacheKey, $provider);

                return $provider;
            }
        }

        $this->cache($cacheKey, null);

        return null;
    }

    /** @return list<AddressHierarchyDefinition> */
    public function hierarchies(mixed $country): array
    {
        return $this->resolve($country)?->addressHierarchies() ?? [];
    }

    private function cacheKey(mixed $country): ?string
    {
        if ($country instanceof AddressCountry) {
            return 'id:' . (string) $country->getKey();
        }

        if (! is_scalar($country)) {
            return null;
        }

        $value = mb_trim((string) $country);

        return $value === '' ? null : 'value:' . mb_strtolower($value);
    }

    private function cache(?string $key, ?CountryAddressProfile $profile): void
    {
        if ($key === null || ! app()->bound('request')) {
            return;
        }

        $request = request();
        $cache = $request->attributes->get(self::REQUEST_CACHE_KEY, []);
        $cache = is_array($cache) ? $cache : [];
        $cache[$key] = $profile;
        $request->attributes->set(self::REQUEST_CACHE_KEY, $cache);
    }
}
