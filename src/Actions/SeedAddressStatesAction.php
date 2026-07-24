<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\ModelResolver;
use RuntimeException;

class SeedAddressStatesAction
{
    public function execute(): array
    {
        $states = require __DIR__ . '/../../resources/data/states.php';

        if (! is_array($states)) {
            throw new RuntimeException('State data file must return an array.');
        }

        $stateClass = ModelResolver::stateClass();
        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($states as $row) {
            if (! isset($row['name'], $row['country_code'])) {
                $skipped++;

                continue;
            }

            $country = AddressCountry::where('iso2', $row['country_code'])->first();

            if (! $country) {
                $skipped++;

                continue;
            }

            $existing = $stateClass::where('country_id', $country->id)
                ->where('code', $row['code'] ?? $row['name'])
                ->first();

            $attrs = [
                'country_id' => $country->id,
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
