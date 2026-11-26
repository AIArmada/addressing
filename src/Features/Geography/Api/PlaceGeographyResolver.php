<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\Geography\Api;

use AIArmada\Addressing\Data\GooglePlaceAddressComponents;

interface PlaceGeographyResolver
{
    /**
     * @return array{
     *     country_id: string|null,
     *     state_id: string|null,
     *     city_id: string|null,
     *     area_assignments: array<string, string>,
     *     state: string|null,
     *     city: string|null,
     *     inferred_area_roles: list<string>
     * }
     */
    public function resolve(
        GooglePlaceAddressComponents $components,
        ?string $placeName,
        ?string $fallbackCountryId,
        ?string $placeId,
    ): array;
}
