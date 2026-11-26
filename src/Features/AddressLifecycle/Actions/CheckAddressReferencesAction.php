<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\AddressLifecycle\Actions;

use AIArmada\Addressing\Features\AddressLifecycle\Api\AddressReferenceReader;
use AIArmada\Addressing\Features\AddressLifecycle\Support\AddressAreaAssignmentOwnerScope;
use AIArmada\Addressing\Support\ModelResolver;

final readonly class CheckAddressReferencesAction implements AddressReferenceReader
{
    public function hasAreaReferences(string $areaId): bool
    {
        return ModelResolver::addressClass()::query()->withoutOwnerScope()->whereHas(
            'areaAssignments',
            static fn ($query) => $query->withoutGlobalScope(AddressAreaAssignmentOwnerScope::class)->where('address_area_id', $areaId),
        )->exists();
    }

    public function hasCountryReferences(string $countryId): bool
    {
        return ModelResolver::addressClass()::query()->withoutOwnerScope()->where('country_id', $countryId)->exists();
    }
}
