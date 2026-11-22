<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Models\Address;
use AIArmada\Addressing\Models\AddressArea;
use AIArmada\Addressing\Models\AddressCountry;
use Illuminate\Validation\ValidationException;

final readonly class GuardAddressCountryDeletionAction
{
    public function handle(AddressCountry $country): void
    {
        $key = (string) $country->getKey();

        if (AddressArea::query()->where('country_id', $key)->exists()
            || Address::query()->where('country_id', $key)->exists()) {
            throw ValidationException::withMessages([
                'country' => 'Delete or reassign this country\'s areas and addresses before deleting it.',
            ]);
        }
    }
}
