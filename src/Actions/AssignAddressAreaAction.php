<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Models\Address;
use AIArmada\Addressing\Models\AddressArea;
use AIArmada\Addressing\Models\AddressAreaAssignment;
use Illuminate\Validation\ValidationException;

final class AssignAddressAreaAction
{
    public function execute(
        Address $address,
        AddressArea $area,
        string $role,
        bool $isPrimary = false,
        array $metadata = [],
    ): AddressAreaAssignment {
        if ($address->country_code === null || mb_strtoupper(mb_trim($address->country_code)) !== mb_strtoupper(mb_trim($area->country_code))) {
            throw ValidationException::withMessages([
                'address_area_id' => 'The selected address area must belong to the address country.',
            ]);
        }

        if (mb_trim($role) === '') {
            throw ValidationException::withMessages([
                'role' => 'The address area role is required.',
            ]);
        }

        return AddressAreaAssignment::query()->updateOrCreate(
            [
                'address_id' => $address->getKey(),
                'address_area_id' => $area->getKey(),
                'role' => $role,
            ],
            [
                'is_primary' => $isPrimary,
                'metadata' => $metadata !== [] ? $metadata : null,
            ],
        );
    }
}
