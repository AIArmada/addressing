<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Models\Address;
use AIArmada\Addressing\Models\AddressSnapshot;
use Illuminate\Database\Eloquent\Model;

class CreateAddressSnapshotAction
{
    public function execute(
        Model $snapshotable,
        Address | AddressData $address,
        ?string $reason = null,
        ?string $label = null,
    ): AddressSnapshot {
        if ($address instanceof Address) {
            $data = AddressData::from($address->toArray());
            $addressId = $address->id;
        } else {
            $data = $address;
            $addressId = null;
        }

        return AddressSnapshot::create([
            'address_id' => $addressId,
            'snapshotable_type' => $snapshotable->getMorphClass(),
            'snapshotable_id' => $snapshotable->getKey(),
            'reason' => $reason,
            'label' => $label ?? $data->label ?? null,
            'line1' => $data->line1,
            'line2' => $data->line2,
            'line3' => $data->line3,
            'city' => $data->city,
            'state' => $data->state,
            'postcode' => $data->postcode,
            'country' => $data->country,
            'country_code' => $data->countryCode,
            'formatted_address' => $data->formatted,
            'components' => $data->components !== [] ? $data->components : null,
            'latitude' => $data->latitude,
            'longitude' => $data->longitude,
            'provider' => $data->provider,
            'provider_place_id' => $data->providerPlaceId,
            'metadata' => $data->metadata !== [] ? $data->metadata : null,
            'google_maps_url' => $data->googleMapsUrl,
            'waze_url' => $data->wazeUrl,
            'navigation_links' => $data->navigationLinks !== [] ? $data->navigationLinks : null,
        ]);
    }
}
