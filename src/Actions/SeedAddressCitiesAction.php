<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\ModelResolver;
use RuntimeException;

class SeedAddressCitiesAction
{
    public function execute(): array
    {
        $cities = require __DIR__ . '/../../resources/data/cities.php';

        if (! is_array($cities)) {
            throw new RuntimeException('City data file must return an array.');
        }

        $cityClass = ModelResolver::cityClass();
        $stateClass = ModelResolver::stateClass();
        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($cities as $row) {
            if (! isset($row['name'], $row['country_code'])) {
                $skipped++;

                continue;
            }

            $country = AddressCountry::where('iso2', $row['country_code'])->first();

            if (! $country) {
                $skipped++;

                continue;
            }

            $state = null;

            if (isset($row['state_code'])) {
                $state = $stateClass::where('country_id', $country->id)
                    ->where('code', $row['state_code'])
                    ->first();
            }

            $existing = $cityClass::where('country_id', $country->id)
                ->where('name', $row['name'])
                ->first();

            $attrs = [
                'country_id' => $country->id,
                'state_id' => $state?->id,
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
