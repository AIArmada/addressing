<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Models\Address;

final class BuildAddressNavigationLinksAction
{
    /**
     * @return array{
     *     google_maps_url: string|null,
     *     google_maps_source: string|null,
     *     waze_url: string|null,
     *     waze_source: string|null,
     *     links: array<string, mixed>
     * }
     */
    public function execute(Address | AddressData $address): array
    {
        if ($address instanceof Address) {
            $data = AddressData::from($address->toArray());
        } else {
            $data = $address;
        }

        return [
            'google_maps_url' => $this->googleMapsUrl($data),
            'google_maps_source' => $this->googleMapsSource($data),
            'waze_url' => $this->wazeUrl($data),
            'waze_source' => $this->wazeSource($data),
            'links' => $data->navigationLinks,
        ];
    }

    private function googleMapsUrl(AddressData $data): ?string
    {
        if ($data->googleMapsUrl !== null) {
            return $data->googleMapsUrl;
        }

        $manual = data_get($data->navigationLinks, 'google_maps.url');

        if (is_string($manual) && $manual !== '') {
            return $manual;
        }

        if ($data->provider === 'google' && $data->providerPlaceId !== null) {
            $query = $this->coordinateQuery($data) ?? $data->formatted ?? $data->line1;

            if ($query !== null) {
                return 'https://www.google.com/maps/search/?' . http_build_query([
                    'api' => '1',
                    'query' => $query,
                    'query_place_id' => $data->providerPlaceId,
                ]);
            }
        }

        if (($coordinateQuery = $this->coordinateQuery($data)) !== null) {
            return 'https://www.google.com/maps/search/?' . http_build_query([
                'api' => '1',
                'query' => $coordinateQuery,
            ]);
        }

        if ($data->formatted !== null) {
            return 'https://www.google.com/maps/search/?' . http_build_query([
                'api' => '1',
                'query' => $data->formatted,
            ]);
        }

        return null;
    }

    private function wazeUrl(AddressData $data): ?string
    {
        if ($data->wazeUrl !== null) {
            return $data->wazeUrl;
        }

        $manual = data_get($data->navigationLinks, 'waze.url');

        if (is_string($manual) && $manual !== '') {
            return $manual;
        }

        if (($coordinateQuery = $this->coordinateQuery($data)) !== null) {
            return 'https://waze.com/ul?' . http_build_query([
                'll' => $coordinateQuery,
                'navigate' => 'yes',
            ]);
        }

        if ($data->formatted !== null) {
            return 'https://waze.com/ul?' . http_build_query([
                'q' => $data->formatted,
            ]);
        }

        return null;
    }

    private function coordinateQuery(AddressData $data): ?string
    {
        if ($data->latitude === null || $data->longitude === null) {
            return null;
        }

        return $data->latitude . ',' . $data->longitude;
    }

    private function googleMapsSource(AddressData $data): ?string
    {
        if ($data->googleMapsUrl !== null) {
            return 'manual';
        }

        if (data_get($data->navigationLinks, 'google_maps.url') !== null) {
            return 'navigation_links';
        }

        if ($data->provider === 'google' && $data->providerPlaceId !== null) {
            return 'generated_place_id';
        }

        if ($data->latitude !== null && $data->longitude !== null) {
            return 'generated_coordinates';
        }

        if ($data->formatted !== null) {
            return 'generated_formatted_address';
        }

        return null;
    }

    private function wazeSource(AddressData $data): ?string
    {
        if ($data->wazeUrl !== null) {
            return 'manual';
        }

        if (data_get($data->navigationLinks, 'waze.url') !== null) {
            return 'navigation_links';
        }

        if ($data->latitude !== null && $data->longitude !== null) {
            return 'generated_coordinates';
        }

        if ($data->formatted !== null) {
            return 'generated_formatted_address';
        }

        return null;
    }
}
