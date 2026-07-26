<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\ModelResolver;
use RuntimeException;

class SeedAddressStatesAction
{
    public function execute(?array $states = null): array
    {
        if ($states === null) {
            $path = __DIR__ . '/../../resources/data/states.json';

            $states = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        }

        if (! is_array($states)) {
            throw new RuntimeException('State data must be an array.');
        }

        $stateClass = ModelResolver::stateClass();

        // ponytail: cache country lookups by iso2 to avoid per-row queries.
        $countryIds = AddressCountry::query()->pluck('id', 'iso2')->all();

        $created = 0;
        $updated = 0;
        $skipped = 0;
        foreach ($states as $row) {
            if (! isset($row['name'], $row['country_code'])) {
                $skipped++;

                continue;
            }

            $countryId = $countryIds[$row['country_code']] ?? null;

            if ($countryId === null) {
                $skipped++;

                continue;
            }

            $code = $row['state_code'] ?? null;

            $query = $stateClass::where('country_id', $countryId);

            if ($code !== null) {
                $query->where('code', $code);
            } else {
                $query->whereNull('code')->where('name', $row['name']);
            }

            $existing = $query->first();

            $attrs = [
                'country_id' => $countryId,
                'name' => $row['name'],
                'country_code' => $row['country_code'] ?? null,
                'code' => $code,
                'type' => $row['type'] ?? null,
                'label' => $row['name'],
                'latitude' => is_numeric($row['latitude'] ?? null) ? (float) $row['latitude'] : null,
                'longitude' => is_numeric($row['longitude'] ?? null) ? (float) $row['longitude'] : null,
            ];

            if ($existing === null) {
                $stateClass::create($attrs);
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
