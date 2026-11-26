<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

use AIArmada\Addressing\Data\AddressData;

interface AddressNavigationLinks
{
    /** @return array{google_maps_url: ?string, google_maps_source: ?string, waze_url: ?string, waze_source: ?string, links: array<string, mixed>} */
    public function build(AddressData $address): array;
}
