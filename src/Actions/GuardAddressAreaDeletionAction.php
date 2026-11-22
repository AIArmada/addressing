<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Models\Address;
use AIArmada\Addressing\Models\AddressArea;
use AIArmada\Addressing\Models\AddressAreaRelationship;
use Illuminate\Validation\ValidationException;

final readonly class GuardAddressAreaDeletionAction
{
    public function handle(AddressArea $addressArea): void
    {
        $key = (string) $addressArea->getKey();

        if (AddressAreaRelationship::query()
            ->where('parent_address_area_id', $key)
            ->where('relationship_type', 'contains')
            ->exists()) {
            throw ValidationException::withMessages([
                'address_area' => 'Delete or reassign this address area\'s child areas before deleting it.',
            ]);
        }

        if (Address::query()->whereHas(
            'areaAssignments',
            static fn ($query) => $query->where('address_area_id', $key),
        )->exists()) {
            throw ValidationException::withMessages([
                'address_area' => 'This address area is still referenced by one or more addresses.',
            ]);
        }
    }
}
