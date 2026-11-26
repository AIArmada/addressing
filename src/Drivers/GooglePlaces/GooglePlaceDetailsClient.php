<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Drivers\GooglePlaces;

use AIArmada\Addressing\Contracts\GooglePlaceDetailsLookup;
use AIArmada\Addressing\GooglePlaces\GoogleMapsInputValues;
use AIArmada\Addressing\GooglePlaces\GooglePlacesConfiguration;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Throwable;

final readonly class GooglePlaceDetailsClient implements GooglePlaceDetailsLookup
{
    /**
     * @return array<string, mixed>
     */
    public function handle(string $placeId, string $validationKey = 'data.address.google_place_id'): array
    {
        $placeId = mb_trim($placeId);

        if (GoogleMapsInputValues::placeId($placeId) !== $placeId) {
            $this->rejectSelection($validationKey);
        }

        $apiKey = GooglePlacesConfiguration::serverApiKey();

        if (! GooglePlacesConfiguration::isEnabled() || ! GooglePlacesConfiguration::isServerLookupEnabled() || $apiKey === null) {
            $this->rejectSelection($validationKey);
        }

        try {
            $response = Http::acceptJson()
                ->withHeaders([
                    'X-Goog-Api-Key' => $apiKey,
                    'X-Goog-FieldMask' => '*',
                ])
                ->withOptions(['allow_redirects' => false])
                ->connectTimeout(3)
                ->timeout(10)
                ->get('https://places.googleapis.com/v1/places/' . rawurlencode($placeId));
        } catch (Throwable) {
            $this->rejectSelection($validationKey);
        }

        $place = $response->successful() ? $response->json() : null;

        if (! is_array($place) || ! is_string($place['id'] ?? null) || ! hash_equals($placeId, $place['id'])) {
            $this->rejectSelection($validationKey);
        }

        $components = is_array($place['addressComponents'] ?? null) ? $place['addressComponents'] : [];
        $hasCountry = collect($components)->contains(
            static fn (mixed $component): bool => is_array($component)
                && is_array($component['types'] ?? null)
                && in_array('country', $component['types'], true),
        );
        $location = is_array($place['location'] ?? null) ? $place['location'] : [];

        if (! $hasCountry || GoogleMapsInputValues::latitude($location['latitude'] ?? null) === null || GoogleMapsInputValues::longitude($location['longitude'] ?? null) === null) {
            $this->rejectSelection($validationKey);
        }

        return $place;
    }

    private function rejectSelection(string $validationKey): never
    {
        throw ValidationException::withMessages([
            $validationKey => __('Select a Google Maps place that can be verified.'),
        ]);
    }
}
