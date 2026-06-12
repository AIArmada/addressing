<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

class AddressSnapshotData
{
    public function __construct(
        public readonly ?string $addressId = null,
        public readonly ?string $reason = null,
        public readonly ?string $label = null,
        public readonly ?string $line1 = null,
        public readonly ?string $line2 = null,
        public readonly ?string $line3 = null,
        public readonly ?string $city = null,
        public readonly ?string $district = null,
        public readonly ?string $state = null,
        public readonly ?string $postcode = null,
        public readonly ?string $country = null,
        public readonly ?string $countryCode = null,
        public readonly ?string $formattedAddress = null,
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
}
