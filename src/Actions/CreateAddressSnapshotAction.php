<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Models\Address;
use AIArmada\Addressing\Models\AddressSnapshot;
use AIArmada\Addressing\Support\AddressOwnerGuard;
use AIArmada\Addressing\Support\ModelResolver;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class CreateAddressSnapshotAction
{
    public function execute(
        Model $snapshotable,
        Address | AddressData $address,
        ?string $reason = null,
        ?string $label = null,
    ): AddressSnapshot {
        $reason = $this->normalizeReason($reason);

        AddressOwnerGuard::assertAddressableIsWritable(
            $snapshotable->getMorphClass(),
            $snapshotable->getKey(),
        );

        if ($address instanceof Address) {
            AddressOwnerGuard::assertAddressIsWritable($address->getKey());
            $data = AddressData::from($address->attributesToArray());
            $addressId = $address->id;
        } else {
            $data = $address;
            $addressId = null;
        }

        $snapshotClass = ModelResolver::snapshotClass();

        return $snapshotClass::create([
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

    private function normalizeReason(?string $reason): ?string
    {
        if ($reason === null) {
            return null;
        }

        $reason = mb_trim($reason);

        if ($reason === '') {
            throw new InvalidArgumentException('Address snapshot reason must be a non-empty string when provided.');
        }

        return $reason;
    }
}
