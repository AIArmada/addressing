<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\Geography\Actions;

use AIArmada\Addressing\Contracts\AddressAreaDeletionGuard;
use AIArmada\Addressing\Features\AddressLifecycle\Api\AddressReferenceReader;
use AIArmada\Addressing\Support\ModelResolver;
use Illuminate\Validation\ValidationException;

final readonly class GuardAddressAreaDeletionAction implements AddressAreaDeletionGuard
{
    public function __construct(private AddressReferenceReader $addresses) {}

    public function handle(string $key): void
    {
        if (ModelResolver::areaClass()::query()
            ->where('parent_id', $key)
            ->exists()) {
            throw ValidationException::withMessages([
                'address_area' => 'Delete or reassign this address area\'s child areas before deleting it.',
            ]);
        }

        if ($this->addresses->hasAreaReferences($key)) {
            throw ValidationException::withMessages([
                'address_area' => 'This address area is still referenced by one or more addresses.',
            ]);
        }
    }
}
