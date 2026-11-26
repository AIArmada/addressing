<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Drivers\GooglePlaces;

use AIArmada\Addressing\Contracts\GoogleMapsLookup;
use AIArmada\Addressing\GooglePlaces\GoogleMapsInputValues;
use AIArmada\Addressing\GooglePlaces\GoogleMapsTrustedHosts;
use AIArmada\Addressing\GooglePlaces\GoogleMapsUrlParser;
use AIArmada\Addressing\GooglePlaces\GooglePlacesConfiguration;
use AIArmada\Addressing\Support\GeoDistance;
use AIArmada\CommerceSupport\Drivers\Http\PinnedHttpClient;
use AIArmada\CommerceSupport\Support\PublicHttpUrlGuard;
use Illuminate\Support\Facades\Http;
use Throwable;

final readonly class GoogleMapsRemoteLookup implements GoogleMapsLookup
{
    public function __construct(
        private GoogleMapsUrlParser $urls,
        private ?PublicHttpUrlGuard $guard = null,
        private ?PinnedHttpClient $http = null,
    ) {}

    public function resolveUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $guard = $this->guard ?? app(PublicHttpUrlGuard::class);
        $http = $this->http ?? app(PinnedHttpClient::class);
        $currentUrl = $url;
        $lastApprovedUrl = $url;

        for ($redirectDepth = 0; $redirectDepth < 5; $redirectDepth++) {
            $currentUrl = $this->urls->unwrapConsent($currentUrl);

            if ($currentUrl === null || ! GoogleMapsTrustedHosts::isSupportedUrl($currentUrl)) {
                return $lastApprovedUrl;
            }

            $lastApprovedUrl = $currentUrl;

            if (! ($this->urls->parse($currentUrl)['needs_redirect_resolution'] ?? false)) {
                return $currentUrl;
            }

            try {
                $target = $guard->validate($currentUrl);
                $response = $http->send(
                    method: 'GET',
                    target: $target,
                    headers: ['User-Agent' => GooglePlacesConfiguration::userAgent()],
                    connectTimeout: 5,
                    timeout: 10,
                );
            } catch (Throwable) {
                return $currentUrl;
            }

            if (! in_array($response->status(), [301, 302, 303, 307, 308], true)) {
                return $currentUrl;
            }

            $location = $response->header('Location');

            if (! is_string($location) || mb_trim($location) === '') {
                return $currentUrl;
            }

            $nextUrl = $this->urls->resolveRelative($currentUrl, $location);
            $unwrappedNext = $this->urls->unwrapConsent($nextUrl);

            if ($unwrappedNext === null || ! GoogleMapsTrustedHosts::isSupportedUrl($unwrappedNext)) {
                return $currentUrl;
            }

            try {
                $guard->assertAllowed($unwrappedNext);
            } catch (Throwable) {
                return $currentUrl;
            }

            $currentUrl = $unwrappedNext;
        }

        $currentUrl = $this->urls->unwrapConsent($currentUrl);

        if ($currentUrl !== null && GoogleMapsTrustedHosts::isSupportedUrl($currentUrl)) {
            return $currentUrl;
        }

        return $lastApprovedUrl;
    }

    /**
     * @return array{place_id: string, display_name: string|null, lat: float|null, lng: float|null}|null
     */
    public function searchByName(
        string $displayName,
        ?float $lat,
        ?float $lng,
        ?string $countryCode,
    ): ?array {
        if (GooglePlacesConfiguration::serverApiKey() === null) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'X-Goog-Api-Key' => GooglePlacesConfiguration::serverApiKey(),
                'X-Goog-FieldMask' => 'places.id,places.displayName,places.location',
            ])
                ->withOptions(['allow_redirects' => false])
                ->timeout(10)
                ->post('https://places.googleapis.com/v1/places:searchText', array_filter([
                    'textQuery' => $displayName,
                    'regionCode' => $countryCode,
                    'locationBias' => (($lat !== null) && ($lng !== null))
                        ? [
                            'circle' => [
                                'center' => [
                                    'latitude' => $lat,
                                    'longitude' => $lng,
                                ],
                                'radius' => 1500.0,
                            ],
                        ]
                        : null,
                ], static fn (mixed $value): bool => $value !== null));
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $expectedName = $this->normalizePlaceText($displayName);
        /** @var array<int, mixed> $places */
        $places = $response->json('places', []);

        if (! is_array($places)) {
            return null;
        }

        $matches = collect($places)
            ->filter(static fn (mixed $place): bool => is_array($place))
            ->map(static function (array $place): array {
                $location = is_array($place['location'] ?? null) ? $place['location'] : [];

                return [
                    'place_id' => GoogleMapsInputValues::placeId($place['id'] ?? null),
                    'display_name' => GoogleMapsInputValues::displayName($place['displayName'] ?? null),
                    'lat' => GoogleMapsInputValues::latitude($location['latitude'] ?? null),
                    'lng' => GoogleMapsInputValues::longitude($location['longitude'] ?? null),
                ];
            })
            ->filter(static fn (array $place): bool => $place['place_id'] !== null && $place['display_name'] !== null)
            ->filter(function (array $place) use ($expectedName, $lat, $lng): bool {
                if ($this->normalizePlaceText($place['display_name']) !== $expectedName) {
                    return false;
                }

                if (($lat === null) || ($lng === null) || ($place['lat'] === null) || ($place['lng'] === null)) {
                    return true;
                }

                return (GeoDistance::kilometersBetween($lat, $lng, $place['lat'], $place['lng']) * 1000.0) <= 1500.0;
            })
            ->values();

        if ($matches->count() !== 1) {
            return null;
        }

        /** @var array{place_id: string, display_name: string|null, lat: float|null, lng: float|null} $match */
        $match = $matches->first();

        return $match;
    }

    /**
     * @return array{display_name: string|null, lat: float|null, lng: float|null}|null
     */
    public function placeDetails(string $placeId): ?array
    {
        if (GooglePlacesConfiguration::serverApiKey() === null) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'X-Goog-Api-Key' => GooglePlacesConfiguration::serverApiKey(),
                'X-Goog-FieldMask' => 'id,displayName,location',
            ])
                ->withOptions(['allow_redirects' => false])
                ->timeout(10)
                ->get('https://places.googleapis.com/v1/places/' . rawurlencode($placeId));
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful() || $response->json('id') !== $placeId) {
            return null;
        }

        return [
            'display_name' => GoogleMapsInputValues::displayName($response->json('displayName')),
            'lat' => GoogleMapsInputValues::latitude($response->json('location.latitude')),
            'lng' => GoogleMapsInputValues::longitude($response->json('location.longitude')),
        ];
    }

    private function normalizePlaceText(string $value): string
    {
        return (string) str($value)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/i', ' ')
            ->squish();
    }
}
