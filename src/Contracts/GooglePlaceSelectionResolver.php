<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface GooglePlaceSelectionResolver
{
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
    public function handle(array $payload): array;
}
