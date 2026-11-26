<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface GooglePlacePhotoLookup
{
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
    public function handle(string $placeId, array $photos): array;
}
