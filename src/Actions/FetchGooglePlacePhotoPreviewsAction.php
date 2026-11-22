<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\GooglePlaces\GooglePlacesConfiguration;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

final class FetchGooglePlacePhotoPreviewsAction
{
    use AsAction;

    private const int MAX_PREVIEWS = 3;

    /**
     * @param  list<array<string, mixed>>  $photos
     * @return list<array{
     *     preview_url: string,
     *     width: int|null,
     *     height: int|null,
     *     attributions: list<array{display_name: string|null, uri: string|null}>,
     *     google_maps_uri: string|null
     * }>
     */
    public function handle(string $placeId, array $photos): array
    {
        $apiKey = GooglePlacesConfiguration::serverApiKey();

        if (! GooglePlacesConfiguration::isEnabled()
            || ! GooglePlacesConfiguration::isServerLookupEnabled()
            || $apiKey === null
            || ! $this->isValidPlaceId($placeId)) {
            return [];
        }

        $previews = [];
        $seenNames = [];

        foreach (array_slice(array_values($photos), 0, self::MAX_PREVIEWS) as $photo) {
            if (! is_array($photo)) {
                continue;
            }

            $name = $this->photoName($placeId, $photo['name'] ?? null);

            if ($name === null || isset($seenNames[$name])) {
                continue;
            }

            $seenNames[$name] = true;

            try {
                $response = Http::acceptJson()
                    ->withHeaders(['X-Goog-Api-Key' => $apiKey])
                    ->withOptions(['allow_redirects' => false])
                    ->connectTimeout(1)
                    ->timeout(2)
                    ->get($this->mediaEndpoint($name), [
                        'skipHttpRedirect' => 'true',
                        'maxWidthPx' => 800,
                    ]);
            } catch (Throwable) {
                continue;
            }

            if (! $response->successful()) {
                continue;
            }

            $photoUri = data_get($response->json(), 'photoUri');

            if (! is_string($photoUri) || ! $this->isGoogleUserContentUri($photoUri)) {
                continue;
            }

            $googleMapsUri = $this->googleMapsUri($photo['googleMapsUri'] ?? null);

            if ($googleMapsUri === null) {
                continue;
            }

            $previews[] = [
                'preview_url' => $photoUri,
                'width' => $this->dimension($photo['widthPx'] ?? null),
                'height' => $this->dimension($photo['heightPx'] ?? null),
                'attributions' => $this->attributions($photo['authorAttributions'] ?? null),
                'google_maps_uri' => $googleMapsUri,
            ];
        }

        return $previews;
    }

    private function isValidPlaceId(string $placeId): bool
    {
        return $placeId !== ''
            && mb_strlen($placeId) <= 255
            && preg_match('/[\x00-\x20\/\\\\]/', $placeId) !== 1;
    }

    private function photoName(string $placeId, mixed $name): ?string
    {
        if (! is_string($name) || $name === '' || preg_match('/[\x00-\x20?#]/', $name) === 1) {
            return null;
        }

        $segments = explode('/', $name);

        if (count($segments) !== 4
            || $segments[0] !== 'places'
            || $segments[1] !== $placeId
            || $segments[2] !== 'photos'
            || $segments[3] === '') {
            return null;
        }

        return $name;
    }

    private function mediaEndpoint(string $name): string
    {
        $encodedName = implode('/', array_map(rawurlencode(...), explode('/', $name)));

        return 'https://places.googleapis.com/v1/' . $encodedName . '/media';
    }

    private function isGoogleUserContentUri(string $uri): bool
    {
        if (filter_var($uri, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $parts = parse_url($uri);
        $host = mb_strtolower((string) ($parts['host'] ?? ''));

        return mb_strtolower((string) ($parts['scheme'] ?? '')) === 'https'
            && ! isset($parts['user'])
            && ! isset($parts['pass'])
            && ! isset($parts['port'])
            && ($host === 'googleusercontent.com' || str_ends_with($host, '.googleusercontent.com'));
    }

    private function dimension(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        $dimension = (int) $value;

        return $dimension > 0 ? $dimension : null;
    }

    /**
     * @return list<array{display_name: string|null, uri: string|null}>
     */
    private function attributions(mixed $authorAttributions): array
    {
        if (! is_array($authorAttributions)) {
            return [];
        }

        $attributions = [];

        foreach ($authorAttributions as $authorAttribution) {
            if (! is_array($authorAttribution)) {
                continue;
            }

            $displayName = $authorAttribution['displayName'] ?? null;
            $displayName = is_string($displayName) && mb_trim($displayName) !== ''
                ? mb_trim($displayName)
                : null;
            $uri = $this->httpsUri($authorAttribution['uri'] ?? null);

            if ($displayName === null && $uri === null) {
                continue;
            }

            $attributions[] = [
                'display_name' => $displayName,
                'uri' => $uri,
            ];
        }

        return $attributions;
    }

    private function googleMapsUri(mixed $uri): ?string
    {
        $uri = $this->httpsUri($uri);

        if ($uri === null) {
            return null;
        }

        $host = mb_strtolower((string) parse_url($uri, PHP_URL_HOST));

        return $host === 'google.com' || str_ends_with($host, '.google.com')
            ? $uri
            : null;
    }

    private function httpsUri(mixed $uri): ?string
    {
        if (! is_string($uri) || mb_trim($uri) === '') {
            return null;
        }

        $uri = mb_trim($uri);

        if (str_starts_with($uri, '//')) {
            $uri = 'https:' . $uri;
        }

        if (filter_var($uri, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $parts = parse_url($uri);

        if (mb_strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
            || ! isset($parts['host'])
            || isset($parts['user'])
            || isset($parts['pass'])) {
            return null;
        }

        return $uri;
    }
}
