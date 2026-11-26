<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\Geography\Actions;

use AIArmada\Addressing\Contracts\AddressAreaAliasDeleter;
use AIArmada\Addressing\Support\ModelResolver;
use DomainException;
use Illuminate\Validation\ValidationException;

final readonly class DeleteAddressAreaAliasAction implements AddressAreaAliasDeleter
{
    public function execute(string $areaId, string $aliasId): void
    {
        ModelResolver::areaClass()::query()->getConnection()->transaction(function () use ($areaId, $aliasId): void {
            $areaClass = ModelResolver::areaClass();
            $persistedArea = $areaClass::query()->lockForUpdate()->findOrFail($areaId);
            $aliasClass = ModelResolver::areaNameClass();
            $persistedAlias = $aliasClass::query()->lockForUpdate()->findOrFail($aliasId);

            if ($persistedAlias->source !== 'manual') {
                throw ValidationException::withMessages([
                    'alias' => __('Provider-owned aliases can only be removed by their geography provider.'),
                ]);
            }

            if ((string) $persistedAlias->address_area_id !== (string) $persistedArea->getKey()) {
                throw ValidationException::withMessages([
                    'alias' => __('The alias does not belong to the selected area.'),
                ]);
            }

            if (! $persistedAlias->delete()) {
                throw new DomainException('The address area alias could not be deleted.');
            }
        });
    }
}
