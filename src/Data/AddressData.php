<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

use AIArmada\Addressing\Support\AddressAliasMap;

class AddressData
{
    public function __construct(
        public readonly ?string $line1 = null,
        public readonly ?string $line2 = null,
        public readonly ?string $line3 = null,
        public readonly ?string $label = null,
        public readonly ?string $city = null,
        public readonly ?string $state = null,
        public readonly ?string $postcode = null,
        public readonly ?string $country = null,
        public readonly ?string $countryCode = null,
        public readonly ?string $formatted = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly array $components = [],
        public readonly array $metadata = [],
        public readonly ?string $googleMapsUrl = null,
        public readonly ?string $wazeUrl = null,
        public readonly array $navigationLinks = [],
        public readonly ?string $provider = null,
        public readonly ?string $providerPlaceId = null,
        public readonly ?string $countryId = null,
        public readonly ?string $stateId = null,
        public readonly ?string $cityId = null,
    ) {}

    public static function from(array $data): self
    {
        $mapped = AddressAliasMap::normalize($data);

        return new self(
            line1: self::stringOrNull($mapped['line1'] ?? null),
            line2: self::stringOrNull($mapped['line2'] ?? null),
            line3: self::stringOrNull($mapped['line3'] ?? null),
            label: self::stringOrNull($mapped['label'] ?? null),
            city: self::stringOrNull($mapped['city'] ?? null),
            state: self::stringOrNull($mapped['state'] ?? null),
            postcode: self::stringOrNull($mapped['postcode'] ?? null),
            country: self::stringOrNull($mapped['country'] ?? null),
            countryCode: self::upperStringOrNull($mapped['countryCode'] ?? null),
            formatted: self::stringOrNull($mapped['formatted'] ?? null),
            latitude: self::floatOrNull($mapped['latitude'] ?? null),
            longitude: self::floatOrNull($mapped['longitude'] ?? null),
            components: isset($mapped['components']) && is_array($mapped['components']) ? $mapped['components'] : [],
            metadata: isset($mapped['metadata']) && is_array($mapped['metadata']) ? $mapped['metadata'] : [],
            googleMapsUrl: self::stringOrNull($mapped['googleMapsUrl'] ?? null),
            wazeUrl: self::stringOrNull($mapped['wazeUrl'] ?? null),
            navigationLinks: isset($mapped['navigationLinks']) && is_array($mapped['navigationLinks']) ? $mapped['navigationLinks'] : [],
            provider: self::stringOrNull($mapped['provider'] ?? null),
            providerPlaceId: self::stringOrNull($mapped['providerPlaceId'] ?? null),
            countryId: self::stringOrNull($mapped['countryId'] ?? null),
            stateId: self::stringOrNull($mapped['stateId'] ?? null),
            cityId: self::stringOrNull($mapped['cityId'] ?? null),
        );
    }

    public function toArray(): array
    {
        return [
            'line1' => $this->line1,
            'line2' => $this->line2,
            'line3' => $this->line3,
            'label' => $this->label,
            'city' => $this->city,
            'state' => $this->state,
            'postcode' => $this->postcode,
            'country' => $this->country,
            'countryCode' => $this->countryCode,
            'formatted' => $this->formatted,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'components' => $this->components,
            'metadata' => $this->metadata,
            'googleMapsUrl' => $this->googleMapsUrl,
            'wazeUrl' => $this->wazeUrl,
            'navigationLinks' => $this->navigationLinks,
            'provider' => $this->provider,
            'providerPlaceId' => $this->providerPlaceId,
            'countryId' => $this->countryId,
            'stateId' => $this->stateId,
            'cityId' => $this->cityId,
        ];
    }

    /**
     * Return the persisted address fields using the model's snake_case names.
     *
     * @return array<string, mixed>
     */
    public function toModelAttributes(): array
    {
        return [
            'country_id' => $this->countryId,
            'state_id' => $this->stateId,
            'city_id' => $this->cityId,
            'label' => $this->label,
            'line1' => $this->line1,
            'line2' => $this->line2,
            'line3' => $this->line3,
            'city' => $this->city,
            'state' => $this->state,
            'postcode' => $this->postcode,
            'country' => $this->country,
            'country_code' => $this->countryCode,
            'formatted_address' => $this->formatted,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'components' => $this->components,
            'metadata' => $this->metadata,
            'google_maps_url' => $this->googleMapsUrl,
            'waze_url' => $this->wazeUrl,
            'navigation_links' => $this->navigationLinks,
            'provider' => $this->provider,
            'provider_place_id' => $this->providerPlaceId,
        ];
    }

    private static function stringOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = mb_trim((string) $value);

        return $value === '' ? null : $value;
    }

    private static function upperStringOrNull(mixed $value): ?string
    {
        $value = self::stringOrNull($value);

        return $value === null ? null : mb_strtoupper($value);
    }

    private static function floatOrNull(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }
}
