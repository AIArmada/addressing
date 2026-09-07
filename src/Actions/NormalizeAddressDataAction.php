<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Contracts\AddressNormalizer;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressCountryResolver;
use AIArmada\Addressing\Support\ModelResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class NormalizeAddressDataAction implements AddressNormalizer
{
    public function __construct(private readonly AddressCountryResolver $countryResolver) {}

    public function normalize(array $data): AddressData
    {
        $address = AddressData::from($data);
        $country = $this->resolveCountry($address);

        $countryId = $this->modelKey($country) ?? $address->countryId;
        $countryCode = $country?->getAttribute('iso2') ?? $address->countryCode;
        $countryName = $country?->getAttribute('name') ?? $address->country;

        $state = $this->resolveState($address, $countryId);
        $stateId = $this->modelKey($state) ?? $address->stateId;
        $stateName = $state?->getAttribute('name') ?? $address->state;

        $city = $this->resolveCity($address, $countryId, $stateId);

        return new AddressData(
            line1: $address->line1,
            line2: $address->line2,
            line3: $address->line3,
            label: $address->label,
            city: $city?->getAttribute('name') ?? $address->city,
            state: $stateName,
            postcode: $address->postcode,
            country: $countryName,
            countryCode: is_string($countryCode) ? mb_strtoupper($countryCode) : null,
            formatted: $address->formatted,
            latitude: $address->latitude,
            longitude: $address->longitude,
            components: $address->components,
            metadata: $address->metadata,
            googleMapsUrl: $address->googleMapsUrl,
            wazeUrl: $address->wazeUrl,
            navigationLinks: $address->navigationLinks,
            provider: $address->provider,
            providerPlaceId: $address->providerPlaceId,
            countryId: $countryId,
            stateId: $stateId,
            cityId: $this->modelKey($city) ?? $address->cityId,
        );
    }

    private function resolveCountry(AddressData $address): ?Model
    {
        if ($address->countryId !== null) {
            return $this->countryResolver->resolve($address->countryId);
        }

        $country = $this->countryResolver->resolve($address->countryCode);

        if ($country instanceof Model || $address->country === null) {
            return $country;
        }

        $countryClass = ModelResolver::countryClass();

        if (! $this->modelTableExists($countryClass)) {
            return null;
        }

        return $countryClass::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($address->country)])
            ->first();
    }

    private function resolveState(AddressData $address, ?string $countryId): ?Model
    {
        $stateClass = ModelResolver::stateClass();

        if (! $this->modelTableExists($stateClass)) {
            return null;
        }

        if ($address->stateId !== null) {
            $state = $stateClass::query()->find($address->stateId);

            if ($state instanceof Model) {
                $this->assertCountryMatch($state, $countryId, 'state');
            }

            return $state instanceof Model ? $state : null;
        }

        if ($address->state === null || $countryId === null) {
            return null;
        }

        return $stateClass::query()
            ->where('country_id', $countryId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($address->state)])
            ->first();
    }

    private function resolveCity(AddressData $address, ?string $countryId, ?string $stateId): ?Model
    {
        $cityClass = ModelResolver::cityClass();

        if (! $this->modelTableExists($cityClass)) {
            return null;
        }

        if ($address->cityId !== null) {
            $city = $cityClass::query()->find($address->cityId);

            if ($city instanceof Model) {
                $this->assertCountryMatch($city, $countryId, 'city');

                if ($stateId !== null && (string) $city->getAttribute('state_id') !== $stateId) {
                    throw new InvalidArgumentException('The address city does not belong to the selected state.');
                }
            }

            return $city instanceof Model ? $city : null;
        }

        if ($address->city === null || $countryId === null) {
            return null;
        }

        $query = $cityClass::query()
            ->where('country_id', $countryId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($address->city)]);

        if ($stateId !== null) {
            $query->where('state_id', $stateId);
        }

        return $query->first();
    }

    private function assertCountryMatch(Model $model, ?string $countryId, string $field): void
    {
        if ($countryId !== null && (string) $model->getAttribute('country_id') !== $countryId) {
            throw new InvalidArgumentException(sprintf('The address %s does not belong to the selected country.', $field));
        }
    }

    private function modelKey(?Model $model): ?string
    {
        if ($model === null || $model->getKey() === null) {
            return null;
        }

        return (string) $model->getKey();
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function modelTableExists(string $modelClass): bool
    {
        /** @var Model $model */
        $model = new $modelClass;

        return Schema::hasTable($model->getTable());
    }
}
