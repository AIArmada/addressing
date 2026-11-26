<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\Geography\Actions;

use AIArmada\Addressing\Contracts\CountryStateSeeder;
use AIArmada\Addressing\Data\CountryStateSeedData;
use AIArmada\Addressing\Support\ModelResolver;
use InvalidArgumentException;

final readonly class SeedCountryStatesAction implements CountryStateSeeder
{
    public function execute(string $countryId, CountryStateSeedData $data): void
    {
        $stateClass = ModelResolver::stateClass();
        $countryClass = ModelResolver::countryClass();
        (new $countryClass)->getConnection()->transaction(function () use ($countryId, $countryClass, $stateClass, $data): void {
            $persisted = $countryClass::query()->whereKey($countryId)->lockForUpdate()->firstOrFail();
            $existing = $stateClass::query()->where('country_id', $persisted->getKey())->whereIn('code', array_column($data->states, 'code'))->lockForUpdate()->get()->keyBy('code');

            foreach ($data->states as $state) {
                if (! is_string($state['code'] ?? null) || mb_trim($state['code']) === '' || ! is_string($state['name'] ?? null) || mb_trim($state['name']) === '') {
                    throw new InvalidArgumentException('State definitions must contain a code and name.');
                }

                $record = $existing->get($state['code']) ?? new $stateClass;
                $record->fill([
                    'country_id' => $persisted->getKey(),
                    'code' => $state['code'],
                    'name' => $state['name'],
                    'country_code' => $persisted->iso2,
                ]);

                if (! $record->exists || $record->isDirty()) {
                    $record->saveOrFail();
                }
            }

            $retired = array_values(array_unique(array_filter(array_map(
                static fn (mixed $code): string => mb_trim((string) $code),
                $data->retiredCodes,
            ), static fn (string $code): bool => $code !== '')));

            if ($retired !== []) {
                foreach ($stateClass::query()->where('country_id', $countryId)->whereIn('code', $retired)->lazyById(500) as $state) {
                    if (! $state->delete()) {
                        throw new InvalidArgumentException('The retired state could not be deleted.');
                    }
                }
            }
        });
    }
}
