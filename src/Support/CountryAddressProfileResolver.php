<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Contracts\CountryAddressProfile;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressCountry;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

final class CountryAddressProfileResolver
{
    public function __construct(
        private readonly Container $container,
        private readonly AddressCountryResolver $countryResolver,
    ) {}

    public function resolve(mixed $country): ?CountryAddressProfile
    {
        $resolvedCountry = $this->countryResolver->resolve($country);

        if (! $resolvedCountry instanceof AddressCountry) {
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
                return $provider;
            }
        }

        return null;
    }

    /**
     * @return list<AddressLevelDefinition>
     */
    public function levels(mixed $country): array
    {
        return $this->resolve($country)?->addressLevels() ?? [];
    }
}
