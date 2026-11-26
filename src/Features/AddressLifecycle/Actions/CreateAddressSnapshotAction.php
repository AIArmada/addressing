<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\AddressLifecycle\Actions;

use AIArmada\Addressing\Contracts\AddressSnapshotWriter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Features\AddressLifecycle\Models\AddressSnapshot;
use AIArmada\Addressing\Features\AddressLifecycle\Support\AddressOwnerGuard;
use InvalidArgumentException;
use RuntimeException;

final readonly class CreateAddressSnapshotAction implements AddressSnapshotWriter
{
    public function execute(
        string $snapshotableType,
        string | int $snapshotableId,
        string | AddressData $address,
        ?string $reason = null,
        ?string $label = null,
    ): string {
        $reason = $this->normalizeReason($reason);
        $snapshotClass = AddressSnapshot::configuredClass();

        return (new $snapshotClass)->getConnection()->transaction(function () use ($snapshotClass, $snapshotableType, $snapshotableId, $address, $reason, $label): string {
            AddressOwnerGuard::lockAddressableForWrite($snapshotableType, $snapshotableId);

            if (is_string($address)) {
                $record = AddressOwnerGuard::lockAddressForWrite($address);
                $data = AddressData::from($record->attributesToArray());
                $addressId = (string) $record->getKey();
            } else {
                $data = AddressData::from($address->toArray());
                $addressId = null;
            }

            $snapshot = new $snapshotClass;
            $snapshot->fill([
                'address_id' => $addressId,
                'snapshotable_type' => $snapshotableType,
                'snapshotable_id' => $snapshotableId,
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
                'formatted_lines' => $data->formatted !== null && mb_trim($data->formatted) !== ''
                    ? explode("\n", $data->formatted)
                    : null,
                'components' => $data->components !== [] ? $data->components : null,
                'latitude' => $data->latitude,
                'longitude' => $data->longitude,
                'google_place_id' => $data->googlePlaceId,
                'google_feature_id' => $data->googleFeatureId,
                'google_cid' => $data->googleCid,
                'google_entity_id' => $data->googleEntityId,
                'metadata' => $data->metadata !== [] ? $data->metadata : null,
                'google_maps_url' => $data->googleMapsUrl,
                'waze_url' => $data->wazeUrl,
                'navigation_links' => $data->navigationLinks !== [] ? $data->navigationLinks : null,
            ]);

            if (! $snapshot->save()) {
                throw new RuntimeException('The address snapshot could not be created.');
            }

            return (string) $snapshot->getKey();
        });
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
