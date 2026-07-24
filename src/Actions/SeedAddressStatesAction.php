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
        $states ??= require __DIR__ . '/../../resources/data/states.php';

        if (! is_array($states)) {
            throw new RuntimeException('State data file must return an array.');
        }

        $stateClass = ModelResolver::stateClass();

        // ponytail: 5k rows — cache country lookups to avoid per-row queries.
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

            $query = $stateClass::where('country_id', $countryId);

            if (isset($row['code'])) {
                $query->where('code', $row['code']);
            } else {
                $query->whereNull('code')->where('name', $row['name']);
            }

            $existing = $query->first();

            $attrs = [
                'country_id' => $countryId,
                'name' => $row['name'],
                'code' => $row['code'] ?? null,
                'type' => $row['type'] ?? null,
                'label' => $row['label'] ?? $row['name'],
                'latitude' => $row['latitude'] ?? null,
                'longitude' => $row['longitude'] ?? null,
                'metadata' => $row['metadata'] ?? null,
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
