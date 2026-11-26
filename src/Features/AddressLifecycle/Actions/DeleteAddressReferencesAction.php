<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\AddressLifecycle\Actions;

use AIArmada\Addressing\Features\AddressLifecycle\Api\AddressDeletionCleanup;
use AIArmada\Addressing\Features\AddressLifecycle\Models\AddressSnapshot;
use AIArmada\Addressing\Features\AddressLifecycle\Support\AddressOwnerGuard;
use AIArmada\Addressing\Support\ModelResolver;
use RuntimeException;

final readonly class DeleteAddressReferencesAction implements AddressDeletionCleanup
{
    public function execute(string $addressId): void
    {
        $addressClass = ModelResolver::addressClass();

        (new $addressClass)->getConnection()->transaction(function () use ($addressId): void {
            $address = AddressOwnerGuard::lockAddressForWrite($addressId);

            foreach ($address->areaAssignments()->get() as $assignment) {
                if (! $assignment->delete()) {
                    throw new RuntimeException('An address area assignment could not be removed.');
                }
            }

            foreach ($address->addressableLinks()->forOwner()->get() as $link) {
                if (! $link->delete()) {
                    throw new RuntimeException('An address attachment could not be removed.');
                }
            }

            AddressSnapshot::configuredClass()::query()->forOwner()->where('address_id', $addressId)->update(['address_id' => null]);
        });
    }
}
