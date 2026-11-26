<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface GoogleMapsInputNormalizer
{
    /**
     * @param  array<string, mixed>  $input
     * @return array{
     *     google_maps_url: string|null,
     *     google_place_id: string|null,
     *     google_feature_id: string|null,
     *     google_cid: string|null,
     *     google_entity_id: string|null,
     *     google_display_name: string|null,
     *     lat: float|null,
     *     lng: float|null,
     *     google_resolution_source: string|null,
     *     google_resolution_status: 'resolved'|'partial'|'unresolved',
     *     google_resolution_fingerprint: string|null,
     *     google_resolution_message: string|null
     * }
     */
    public function handle(array $input): array;
}
