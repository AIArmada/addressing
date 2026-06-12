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
        public readonly ?string $city = null,
        public readonly ?string $district = null,
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
    ) {}

    public static function from(array $data): self
    {
        $mapped = AddressAliasMap::normalize($data);

        return new self(
            line1: self::stringOrNull($mapped['line1'] ?? null),
            line2: self::stringOrNull($mapped['line2'] ?? null),
            line3: self::stringOrNull($mapped['line3'] ?? null),
            city: self::stringOrNull($mapped['city'] ?? null),
            district: self::stringOrNull($mapped['district'] ?? null),
            state: self::stringOrNull($mapped['state'] ?? null),
            postcode: self::stringOrNull($mapped['postcode'] ?? null),
            country: self::stringOrNull($mapped['country'] ?? null),
            countryCode: self::stringOrNull($mapped['countryCode'] ?? null),
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
        );
    }

    public function toArray(): array
    {
        return [
            'line1' => $this->line1,
            'line2' => $this->line2,
            'line3' => $this->line3,
            'city' => $this->city,
            'district' => $this->district,
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
        ];
    }

    private static function stringOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    private static function floatOrNull(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }
}
