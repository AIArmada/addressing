<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\ModelResolver;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
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

        $existingByKey = $stateClass::query()
            ->get(['id', 'country_id', 'name', 'country_code', 'code', 'latitude', 'longitude'])
            ->keyBy(fn ($state): string => self::naturalKey($state->country_id, $state->code, $state->name));

        $inserts = [];

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

            $attrs = [
                'country_id' => $countryId,
                'name' => $row['name'],
                'country_code' => $row['country_code'] ?? null,
                'code' => $code,
                'latitude' => is_numeric($row['latitude'] ?? null) ? (float) $row['latitude'] : null,
                'longitude' => is_numeric($row['longitude'] ?? null) ? (float) $row['longitude'] : null,
            ];

            $key = self::naturalKey($countryId, $code, $row['name']);
            $existing = $existingByKey->get($key);

            if ($existing === null) {
                if (isset($inserts[$key])) {
                    $skipped++;

                    continue;
                }

                $now = CarbonImmutable::now()->toDateTimeString();
                $inserts[$key] = [
                    'id' => (string) Str::orderedUuid(),
                    ...$attrs,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $created++;

                continue;
            }

            $existing->fill($attrs);

            if ($existing->isDirty()) {
                $existing->save();
                $updated++;
            } else {
                $skipped++;
            }
        }

        foreach (array_chunk(array_values($inserts), 1000) as $insertChunk) {
            $stateClass::query()->insert($insertChunk);
        }

        return compact('created', 'updated', 'skipped');
    }

    private static function naturalKey(string $countryId, ?string $code, string $name): string
    {
        return $code !== null
            ? 'code|' . $countryId . '|' . $code
            : 'name|' . $countryId . '|' . $name;
    }
}
