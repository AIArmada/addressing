<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\Geography\Actions;

use AIArmada\Addressing\Contracts\AddressAreaAliasWriter;
use AIArmada\Addressing\Support\ModelResolver;
use DomainException;
use Illuminate\Validation\ValidationException;

final readonly class SaveAddressAreaAliasAction implements AddressAreaAliasWriter
{
    /**
     * @param  array{name: mixed, name_type?: mixed, is_preferred?: mixed}  $attributes
     */
    public function handle(string $areaId, array $attributes, ?string $aliasId = null): string
    {
        $name = $attributes['name'] ?? null;

        if (! is_string($name) || mb_trim($name) === '') {
            throw ValidationException::withMessages([
                'name' => __('An alias name is required.'),
            ]);
        }

        if (mb_strlen(mb_trim($name)) > 255) {
            throw ValidationException::withMessages([
                'name' => __('The alias name may not be greater than 255 characters.'),
            ]);
        }

        return ModelResolver::areaClass()::query()->getConnection()->transaction(function () use ($areaId, $attributes, $name, $aliasId): string {
            $areaClass = ModelResolver::areaClass();
            $persistedArea = $areaClass::query()->lockForUpdate()->findOrFail($areaId);
            $aliasClass = ModelResolver::areaNameClass();

            if ($aliasId !== null) {
                $persistedAlias = $aliasClass::query()->lockForUpdate()->findOrFail($aliasId);

                if ($persistedAlias->source !== 'manual') {
                    throw ValidationException::withMessages([
                        'name' => __('Provider-owned aliases can only be changed by their geography provider.'),
                    ]);
                }

                if ((string) $persistedAlias->address_area_id !== (string) $persistedArea->getKey()) {
                    throw ValidationException::withMessages([
                        'name' => __('The alias does not belong to the selected area.'),
                    ]);
                }

                $record = $persistedAlias;
            } else {
                $record = new $aliasClass;
            }

            $nameType = $attributes['name_type'] ?? $record->name_type ?? 'alternative';

            if (! is_string($nameType) || mb_trim($nameType) === '' || mb_strlen(mb_trim($nameType)) > 32) {
                throw ValidationException::withMessages([
                    'name_type' => __('The alias type must be a string of at most 32 characters.'),
                ]);
            }

            $isPreferred = $attributes['is_preferred'] ?? $record->is_preferred ?? false;

            if (! is_bool($isPreferred)) {
                throw ValidationException::withMessages([
                    'is_preferred' => __('The preferred flag must be a boolean.'),
                ]);
            }

            $record->fill([
                'address_area_id' => $persistedArea->getKey(),
                'name' => mb_trim($name),
                'source' => 'manual',
                'name_type' => mb_trim($nameType),
                'is_preferred' => $isPreferred,
            ]);

            if (! $record->save()) {
                throw new DomainException('The address area alias could not be saved.');
            }

            return (string) $record->getKey();
        });
    }
}
