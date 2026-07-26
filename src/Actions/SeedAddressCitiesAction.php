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
        if ($cities === null) {
            $path = __DIR__ . '/../../resources/data/cities.json';

            $cities = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        }

        if (! is_array($cities)) {
            throw new RuntimeException('City data must be an array.');
        }

        $cityClass = ModelResolver::cityClass();
        $stateClass = ModelResolver::stateClass();

        // ponytail: 150k+ rows — cache country/state lookups.
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

            $stateCode = $row['state_code'] ?? null;
            $stateId = $stateCode !== null
                ? ($stateIds[$countryId . '|' . $stateCode] ?? null)
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
                'country_code' => $row['country_code'] ?? null,
                'state_code' => $stateCode,
                'label' => $row['name'],
                'latitude' => is_numeric($row['latitude'] ?? null) ? (float) $row['latitude'] : null,
                'longitude' => is_numeric($row['longitude'] ?? null) ? (float) $row['longitude'] : null,
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
