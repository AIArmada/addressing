<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Models\Address;
use AIArmada\Addressing\Support\NormalizeNavigationUrl;

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

        $google = $this->resolveGoogleMaps($data);
        $waze = $this->resolveWaze($data);

        return [
            'google_maps_url' => $google['url'],
            'google_maps_source' => $google['source'],
            'waze_url' => $waze['url'],
            'waze_source' => $waze['source'],
            'links' => $data->navigationLinks,
        ];
    }

    /**
     * @return array{url: string|null, source: string|null}
     */
    private function resolveGoogleMaps(AddressData $data): array
    {
        $normalizer = new NormalizeNavigationUrl;

        if ($data->googleMapsUrl !== null) {
            return [
                'url' => $normalizer->normalize($data->googleMapsUrl),
                'source' => 'manual',
            ];
        }

        $manual = data_get($data->navigationLinks, 'google_maps.url');

        if (is_string($manual) && $manual !== '') {
            return [
                'url' => $normalizer->normalize($manual),
                'source' => 'navigation_links',
            ];
        }

        if ($data->provider === 'google' && $data->providerPlaceId !== null) {
            $query = $this->coordinateQuery($data) ?? $data->formatted ?? $data->line1;

            if ($query !== null) {
                return [
                    'url' => $normalizer->normalize('https://www.google.com/maps/search/?' . http_build_query([
                        'api' => '1',
                        'query' => $query,
                        'query_place_id' => $data->providerPlaceId,
                    ])),
                    'source' => 'generated_place_id',
                ];
            }
        }

        if (($coordinateQuery = $this->coordinateQuery($data)) !== null) {
            return [
                'url' => $normalizer->normalize('https://www.google.com/maps/search/?' . http_build_query([
                    'api' => '1',
                    'query' => $coordinateQuery,
                ])),
                'source' => 'generated_coordinates',
            ];
        }

        if ($data->formatted !== null) {
            return [
                'url' => $normalizer->normalize('https://www.google.com/maps/search/?' . http_build_query([
                    'api' => '1',
                    'query' => $data->formatted,
                ])),
                'source' => 'generated_formatted_address',
            ];
        }

        return ['url' => null, 'source' => null];
    }

    /**
     * @return array{url: string|null, source: string|null}
     */
    private function resolveWaze(AddressData $data): array
    {
        $normalizer = new NormalizeNavigationUrl;

        if ($data->wazeUrl !== null) {
            return [
                'url' => $normalizer->normalize($data->wazeUrl),
                'source' => 'manual',
            ];
        }

        $manual = data_get($data->navigationLinks, 'waze.url');

        if (is_string($manual) && $manual !== '') {
            return [
                'url' => $normalizer->normalize($manual),
                'source' => 'navigation_links',
            ];
        }

        if (($coordinateQuery = $this->coordinateQuery($data)) !== null) {
            return [
                'url' => $normalizer->normalize('https://waze.com/ul?' . http_build_query([
                    'll' => $coordinateQuery,
                    'navigate' => 'yes',
                ])),
                'source' => 'generated_coordinates',
            ];
        }

        if ($data->formatted !== null) {
            return [
                'url' => $normalizer->normalize('https://waze.com/ul?' . http_build_query([
                    'q' => $data->formatted,
                ])),
                'source' => 'generated_formatted_address',
            ];
        }

        return ['url' => null, 'source' => null];
    }

    private function coordinateQuery(AddressData $data): ?string
    {
        if ($data->latitude === null || $data->longitude === null) {
            return null;
        }

        return $data->latitude . ',' . $data->longitude;
    }
}
