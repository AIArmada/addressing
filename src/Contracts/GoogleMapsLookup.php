<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface GoogleMapsLookup
{
    public function resolveUrl(?string $url): ?string;

    /** @return array{place_id: string, display_name: string|null, lat: float|null, lng: float|null}|null */
    public function searchByName(string $displayName, ?float $lat, ?float $lng, ?string $countryCode): ?array;

    /** @return array{display_name: string|null, lat: float|null, lng: float|null}|null */
    public function placeDetails(string $placeId): ?array;
}
