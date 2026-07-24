<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\ModelResolver;
use RuntimeException;

class SeedAddressCitiesAction
{
    public function execute(?array $cities = null): array
    {
        $cities ??= require __DIR__ . '/../../resources/data/cities.php';

        if (! is_array($cities)) {
            throw new RuntimeException('City data file must return an array.');
        }

        $cityClass = ModelResolver::cityClass();
        $stateClass = ModelResolver::stateClass();

        // ponytail: 150k+ rows — cache country/state lookups to avoid ~300k queries.
        $countryIds = AddressCountry::query()->pluck('id', 'iso2')->all();
        $stateIds = $stateClass::query()
            ->get(['id', 'country_id', 'code'])
            ->mapWithKeys(fn ($state) => [$state->country_id . '|' . $state->code => $state->id])
            ->all();

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($cities as $row) {
            if (! isset($row['name'], $row['country_code'])) {
                $skipped++;

                continue;
            }

            $countryId = $countryIds[$row['country_code']] ?? null;

            if ($countryId === null) {
                $skipped++;

                continue;
            }

            $stateId = isset($row['state_code'])
                ? ($stateIds[$countryId . '|' . $row['state_code']] ?? null)
                : null;

            $query = $cityClass::where('country_id', $countryId)->where('name', $row['name']);

            if ($stateId !== null) {
                $query->where('state_id', $stateId);
            } else {
                $query->whereNull('state_id');
            }

            $existing = $query->first();

            $attrs = [
                'country_id' => $countryId,
                'state_id' => $stateId,
                'name' => $row['name'],
                'label' => $row['label'] ?? $row['name'],
                'latitude' => $row['latitude'] ?? null,
                'longitude' => $row['longitude'] ?? null,
                'metadata' => $row['metadata'] ?? null,
            ];

            if ($existing === null) {
                $cityClass::create($attrs);
                $created++;
            } else {
                $existing->fill($attrs);

                if ($existing->isDirty()) {
                    $existing->save();
                    $updated++;
                } else {
                    $skipped++;
                }
            }
        }

        return compact('created', 'updated', 'skipped');
    }
}
