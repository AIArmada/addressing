<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

class AddressAliasMap
{
    private const ALIASES = [
        'address_line_1' => 'line1',
        'address_line_2' => 'line2',
        'street_address' => 'line1',
        'shipping_street_address' => 'line1',
        'postal_code' => 'postcode',
        'zip_code' => 'postcode',
        'postCode' => 'postcode',
        'countryCode' => 'countryCode',
        'country_code' => 'countryCode',
        'lat' => 'latitude',
        'lng' => 'longitude',
        'lon' => 'longitude',
        'formatted_address' => 'formatted',
        'formattedAddress' => 'formatted',
        'google_maps_url' => 'googleMapsUrl',
        'googleMapsUrl' => 'googleMapsUrl',
        'google_map_url' => 'googleMapsUrl',
        'googleMapUrl' => 'googleMapsUrl',
        'maps_url' => 'googleMapsUrl',
        'mapsUrl' => 'googleMapsUrl',
        'waze_url' => 'wazeUrl',
        'wazeUrl' => 'wazeUrl',
        'navigation_links' => 'navigationLinks',
        'navigationLinks' => 'navigationLinks',
        'external_links' => 'navigationLinks',
        'externalLinks' => 'navigationLinks',
        'provider' => 'provider',
        'provider_place_id' => 'providerPlaceId',
        'providerPlaceId' => 'providerPlaceId',
        'place_id' => 'providerPlaceId',
        'placeId' => 'providerPlaceId',
        'google_place_id' => 'providerPlaceId',
        'googlePlaceId' => 'providerPlaceId',
    ];

    public static function normalize(array $data): array
    {
        $mapped = [];

        foreach ($data as $key => $value) {
            $targetKey = self::ALIASES[$key] ?? $key;
            $mapped[$targetKey] = $value;
        }

        return $mapped;
    }
}
