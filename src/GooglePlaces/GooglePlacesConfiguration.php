<?php

declare(strict_types=1);

namespace AIArmada\Addressing\GooglePlaces;

final class GooglePlacesConfiguration
{
    public static function apiKey(): ?string
    {
        $apiKey = config('addressing.google_places.api_key')
            ?? config('services.google.maps_api_key');

        if (! is_string($apiKey)) {
            return null;
        }

        $apiKey = mb_trim($apiKey);

        return $apiKey === '' ? null : $apiKey;
    }

    public static function isEnabled(): bool
    {
        $enabled = config('addressing.google_places.enabled');
        $resolved = $enabled !== null
            ? (bool) $enabled
            : (bool) config('services.google.places_enabled', false);

        return $resolved && self::apiKey() !== null;
    }

    public static function serverApiKey(): ?string
    {
        $apiKey = config('addressing.google_places.server_api_key')
            ?? config('services.google.places_server_api_key');

        if (! is_string($apiKey)) {
            return null;
        }

        $apiKey = mb_trim($apiKey);

        return $apiKey === '' ? null : $apiKey;
    }

    public static function isLinkResolutionEnabled(): bool
    {
        $enabled = config('addressing.google_places.link_resolution_enabled');

        return $enabled !== null
            ? (bool) $enabled
            : (bool) config('services.google.place_link_resolution_enabled', false);
    }

    public static function isServerLookupEnabled(): bool
    {
        return self::isLinkResolutionEnabled() && self::serverApiKey() !== null;
    }

    public static function userAgent(): string
    {
        $configured = config('addressing.google_places.user_agent');

        if (is_string($configured) && mb_trim($configured) !== '') {
            return mb_trim($configured);
        }

        $appUrl = config('app.url');
        $suffix = is_string($appUrl) && $appUrl !== '' ? ' (+' . $appUrl . ')' : '';

        return 'AIArmada-Addressing/1.0' . $suffix;
    }
}
