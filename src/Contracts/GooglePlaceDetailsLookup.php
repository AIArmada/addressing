<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface GooglePlaceDetailsLookup
{
    /**
     * @return array<string, mixed>
     */
    public function handle(string $placeId, string $validationKey = 'data.address.google_place_id'): array;
}
