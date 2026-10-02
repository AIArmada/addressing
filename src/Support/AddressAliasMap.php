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
        'countryId' => 'countryId',
        'country_id' => 'countryId',
        'stateId' => 'stateId',
        'state_id' => 'stateId',
        'cityId' => 'cityId',
        'city_id' => 'cityId',
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
        'google_place_id' => 'googlePlaceId',
        'googlePlaceId' => 'googlePlaceId',
        'place_id' => 'googlePlaceId',
        'placeId' => 'googlePlaceId',
        'google_feature_id' => 'googleFeatureId',
        'googleFeatureId' => 'googleFeatureId',
        'google_cid' => 'googleCid',
        'googleCid' => 'googleCid',
        'google_entity_id' => 'googleEntityId',
        'googleEntityId' => 'googleEntityId',
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
