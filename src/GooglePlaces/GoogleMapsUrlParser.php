<?php

declare(strict_types=1);

namespace AIArmada\Addressing\GooglePlaces;

final class GoogleMapsUrlParser
{
    public function canonicalUrl(?string $placeId, ?float $lat, ?float $lng, ?string $displayName): ?string
    {
        if (($lat !== null) && ($lng !== null)) {
            $parameters = [
                'api' => '1',
                'query' => $this->formatCoordinate($lat) . ',' . $this->formatCoordinate($lng),
            ];

            if ($placeId !== null) {
                $parameters['query_place_id'] = $placeId;
            }

            return 'https://www.google.com/maps/search/?' . http_build_query($parameters);
        }

        if ($placeId === null) {
            return null;
        }

        return 'https://www.google.com/maps/search/?' . http_build_query([
            'api' => '1',
            'query' => $displayName ?? $placeId,
            'query_place_id' => $placeId,
        ]);
    }

    /**
     * @return array{
     *     url: string|null,
     *     place_id: string|null,
     *     display_name: string|null,
     *     lat: float|null,
     *     lng: float|null,
     *     needs_redirect_resolution: bool
     * }
     */
    public function parse(?string $url): array
    {
        $trimmedUrl = GoogleMapsInputValues::string($url);

        if ($trimmedUrl === null) {
            return [
                'url' => null,
                'place_id' => null,
                'display_name' => null,
                'lat' => null,
                'lng' => null,
                'needs_redirect_resolution' => false,
            ];
        }

        $host = parse_url($trimmedUrl, PHP_URL_HOST);
        $host = is_string($host) ? mb_strtolower($host) : null;
        $query = parse_url($trimmedUrl, PHP_URL_QUERY);
        $path = parse_url($trimmedUrl, PHP_URL_PATH);
        $path = is_string($path) ? $path : '';

        $queryParams = [];

        if (is_string($query) && $query !== '') {
            parse_str($query, $queryParams);
        }

        $placeId = GoogleMapsInputValues::placeId($queryParams['query_place_id'] ?? null);
        $displayName = $this->extractDisplayNameFromPath($path);
        $coordinates = $this->extractCoordinatesFromGoogleMapsUrl($trimmedUrl);

        foreach (['q', 'query', 'destination'] as $queryKey) {
            $queryValue = GoogleMapsInputValues::string($queryParams[$queryKey] ?? null);

            if ($queryValue === null) {
                continue;
            }

            $placeId ??= $this->extractPlaceIdFromQueryValue($queryValue);

            $parsedQuery = $this->extractQueryDisplayNameAndCoordinates($queryValue);

            $displayName ??= $parsedQuery['display_name'];
            $coordinates ??= $parsedQuery['coordinates'];
        }

        return [
            'url' => $trimmedUrl,
            'place_id' => $placeId,
            'display_name' => $displayName,
            'lat' => $coordinates['lat'] ?? null,
            'lng' => $coordinates['lng'] ?? null,
            'needs_redirect_resolution' => $this->needsRedirectResolution($host, $queryParams),
        ];
    }

    /**
     * @param  array<string, mixed>  $queryParams
     */
    private function needsRedirectResolution(?string $host, array $queryParams): bool
    {
        if ($host === null) {
            return false;
        }

        if (GoogleMapsTrustedHosts::isShortenerHost($host)) {
            return true;
        }

        return GoogleMapsTrustedHosts::isMapsHost($host) && filled($queryParams['cid'] ?? null);
    }

    public function unwrapConsent(?string $url): ?string
    {
        $currentUrl = $url;

        for ($redirectDepth = 0; $redirectDepth < 3; $redirectDepth++) {
            $host = parse_url((string) $currentUrl, PHP_URL_HOST);
            $host = is_string($host) ? mb_strtolower($host) : '';

            if ($host !== 'consent.google.com') {
                return $currentUrl;
            }

            $query = parse_url((string) $currentUrl, PHP_URL_QUERY);

            if (! is_string($query) || $query === '') {
                return $currentUrl;
            }

            parse_str($query, $parameters);

            $continueUrl = $parameters['continue'] ?? null;

            if (! is_string($continueUrl) || mb_trim($continueUrl) === '') {
                return $currentUrl;
            }

            $currentUrl = str_starts_with($continueUrl, '/')
                ? 'https://www.google.com' . $continueUrl
                : $continueUrl;

            if (! GoogleMapsTrustedHosts::isSupportedUrl($currentUrl)) {
                return $url;
            }
        }

        return $currentUrl;
    }

    public function resolveRelative(string $baseUrl, string $location): string
    {
        if (preg_match('/^https?:\/\//i', $location) === 1) {
            return $location;
        }

        $baseParts = parse_url($baseUrl);
        $scheme = is_string($baseParts['scheme'] ?? null) ? $baseParts['scheme'] : 'https';
        $host = is_string($baseParts['host'] ?? null) ? $baseParts['host'] : '';
        $port = isset($baseParts['port']) ? ':' . $baseParts['port'] : '';

        if (str_starts_with($location, '//')) {
            return $scheme . ':' . $location;
        }

        if (str_starts_with($location, '/')) {
            return $scheme . '://' . $host . $port . $location;
        }

        $basePath = is_string($baseParts['path'] ?? null) ? $baseParts['path'] : '/';
        $directory = mb_rtrim((string) preg_replace('~/[^/]*$~', '/', $basePath), '/');

        return $scheme . '://' . $host . $port . $directory . '/' . $location;
    }

    private function extractDisplayNameFromPath(string $path): ?string
    {
        if (preg_match('~/maps/place/([^/@]+)~', $path, $matches) !== 1) {
            return null;
        }

        $decoded = str_replace('+', ' ', urldecode($matches[1]));

        return GoogleMapsInputValues::string($decoded);
    }

    private function extractPlaceIdFromQueryValue(string $queryValue): ?string
    {
        if (preg_match('/place_id:([^&]+)/i', $queryValue, $matches) !== 1) {
            return null;
        }

        return GoogleMapsInputValues::placeId($matches[1]);
    }

    /**
     * @return array{display_name: string|null, coordinates: array{lat: float, lng: float}|null}
     */
    private function extractQueryDisplayNameAndCoordinates(string $queryValue): array
    {
        if (preg_match('/(-?\d+\.\d+)\s*,\s*(-?\d+\.\d+)/', $queryValue, $matches, PREG_OFFSET_CAPTURE) === 1) {
            $displayName = mb_substr($queryValue, 0, $matches[0][1]);
            $displayName = mb_trim(preg_replace('/[,\s]+$/', '', $displayName) ?? '');

            return [
                'display_name' => GoogleMapsInputValues::string($displayName),
                'coordinates' => [
                    'lat' => (float) $matches[1][0],
                    'lng' => (float) $matches[2][0],
                ],
            ];
        }

        return [
            'display_name' => GoogleMapsInputValues::string(str_replace('+', ' ', $queryValue)),
            'coordinates' => null,
        ];
    }

    /**
     * @return array{lat: float, lng: float}|null
     */
    private function extractCoordinatesFromGoogleMapsUrl(?string $url): ?array
    {
        if (! is_string($url) || ! filled($url)) {
            return null;
        }

        if (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $url, $matches) === 1) {
            return [
                'lat' => (float) $matches[1],
                'lng' => (float) $matches[2],
            ];
        }

        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches) === 1) {
            return [
                'lat' => (float) $matches[1],
                'lng' => (float) $matches[2],
            ];
        }

        if (preg_match('/(?:query|destination)=[^&#]*?(-?\d+\.\d+)(?:,|%2C)(-?\d+\.\d+)/i', $url, $matches) === 1) {
            return [
                'lat' => (float) $matches[1],
                'lng' => (float) $matches[2],
            ];
        }

        return null;
    }

    private function formatCoordinate(float $value): string
    {
        return mb_rtrim(mb_rtrim(number_format($value, 12, '.', ''), '0'), '.');
    }
}
