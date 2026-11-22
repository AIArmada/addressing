<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\GooglePlaces\GooglePlaceAddressComponents;
use AIArmada\Addressing\GooglePlaces\GooglePlaceGeographyResolver;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class ResolveGooglePlaceSelectionAction
{
    use AsAction;

    public function __construct(
        private readonly NormalizeGoogleMapsInputAction $normalizeGoogleMapsInputAction,
        private readonly GooglePlaceGeographyResolver $geography,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array{
     *     country_id: string|null,
     *     state_id: string|null,
     *     city_id: string|null,
     *     area_assignments: array<string, string>,
     *     line1: string|null,
     *     line2: string|null,
     *     postcode: string|null,
     *     state: string|null,
     *     city: string|null,
     *     google_maps_url: string|null,
     *     google_place_id: string|null,
     *     google_feature_id: string|null,
     *     google_cid: string|null,
     *     google_entity_id: string|null,
     *     google_display_name: string|null,
     *     latitude: float|null,
     *     longitude: float|null,
     *     google_resolution_source: string|null,
     *     google_resolution_status: 'resolved'|'partial'|'unresolved',
     *     google_resolution_fingerprint: string|null,
     *     google_resolution_message: string|null,
     *     google_inferred_area_roles: list<string>
     * }
     */
    public function handle(array $payload): array
    {
        $components = new GooglePlaceAddressComponents($payload['addressComponents'] ?? []);
        $placeName = $this->displayNameValue($payload['displayName'] ?? null);
        $geography = $this->geography->resolve(
            $components,
            $placeName,
            $this->uuidValue($payload['fallbackCountryId'] ?? null),
            $this->stringValue($payload['placeId'] ?? $payload['id'] ?? null),
        );

        $lat = $this->numericValue(Arr::get($payload, 'location.lat'));
        $lng = $this->numericValue(Arr::get($payload, 'location.lng'));
        $googleMapsState = $this->normalizeGoogleMapsInputAction->handle([
            'google_maps_url' => $this->stringValue($payload['googleMapsURI'] ?? null),
            'google_place_id' => $this->stringValue($payload['placeId'] ?? $payload['id'] ?? null),
            'google_feature_id' => $this->stringValue($payload['googleFeatureId'] ?? $payload['google_feature_id'] ?? null),
            'google_cid' => $this->stringValue($payload['googleCid'] ?? $payload['google_cid'] ?? null),
            'google_entity_id' => $this->stringValue($payload['googleEntityId'] ?? $payload['google_entity_id'] ?? null),
            'google_display_name' => $placeName,
            'lat' => $lat,
            'lng' => $lng,
            'google_resolution_source' => 'picker',
            'google_resolution_status' => 'resolved',
        ]);

        return [
            'country_id' => $geography['country_id'],
            'state_id' => $geography['state_id'],
            'city_id' => $geography['city_id'],
            'area_assignments' => $geography['area_assignments'],
            'line1' => $components->line1($placeName),
            'line2' => $components->line2(),
            'postcode' => $components->value(['postal_code']),
            'state' => $geography['state'],
            'city' => $geography['city'],
            'google_maps_url' => $googleMapsState['google_maps_url'],
            'google_place_id' => $googleMapsState['google_place_id'],
            'google_feature_id' => $googleMapsState['google_feature_id'],
            'google_cid' => $googleMapsState['google_cid'],
            'google_entity_id' => $googleMapsState['google_entity_id'],
            'google_display_name' => $googleMapsState['google_display_name'],
            'latitude' => $googleMapsState['lat'],
            'longitude' => $googleMapsState['lng'],
            'google_resolution_source' => $googleMapsState['google_resolution_source'],
            'google_resolution_status' => $googleMapsState['google_resolution_status'],
            'google_resolution_fingerprint' => $googleMapsState['google_resolution_fingerprint'],
            'google_resolution_message' => $googleMapsState['google_resolution_message'],
            'google_inferred_area_roles' => $geography['inferred_area_roles'],
        ];
    }

    private function displayNameValue(mixed $value): ?string
    {
        if (is_array($value)) {
            $text = $value['text'] ?? null;

            return $this->stringValue($text);
        }

        return $this->stringValue($value);
    }

    private function stringValue(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = mb_trim($value);

        return $value === '' ? null : $value;
    }

    private function numericValue(mixed $value): ?float
    {
        if (! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function uuidValue(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = mb_trim($value);

        return Str::isUuid($value) ? $value : null;
    }
}
