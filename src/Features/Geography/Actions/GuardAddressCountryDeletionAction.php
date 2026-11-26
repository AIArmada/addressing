<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\Geography\Actions;

use AIArmada\Addressing\Contracts\AddressCountryDeletionGuard;
use AIArmada\Addressing\Features\AddressLifecycle\Api\AddressReferenceReader;
use AIArmada\Addressing\Support\ModelResolver;
use Illuminate\Validation\ValidationException;

final readonly class GuardAddressCountryDeletionAction implements AddressCountryDeletionGuard
{
    public function __construct(private AddressReferenceReader $addresses) {}

    public function handle(string $key): void
    {
        if (ModelResolver::areaClass()::query()->where('country_id', $key)->exists()
            || ModelResolver::stateClass()::query()->where('country_id', $key)->exists()
            || ModelResolver::cityClass()::query()->where('country_id', $key)->exists()
            || $this->addresses->hasCountryReferences($key)) {
            throw ValidationException::withMessages([
                'country' => 'Delete or reassign this country\'s states, cities, areas, and addresses before deleting it.',
            ]);
        }
    }
}
