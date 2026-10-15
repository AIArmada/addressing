<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Support\CitySeedRowReader;
use AIArmada\Addressing\Support\ModelResolver;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

class SeedAddressCitiesAction
{
    /**
     * @param  ?callable(string, int, int, ?string=): void  $progress
     */
    public function execute(?array $cities = null, ?callable $progress = null): array
    {
        return ModelResolver::cityClass()::query()->getConnection()->transaction(fn (): array => $this->seed($cities, $progress));
    }

    /**
     * @param  array<int, mixed>|null  $cities
     * @param  ?callable(string, int, int, ?string=): void  $progress
     */
    private function seed(?array $cities, ?callable $progress): array
    {
        $chunks = $cities === null
            ? (new CitySeedRowReader(__DIR__ . '/../../resources/data/cities.json'))->readAllChunks(2000)
            : array_chunk($cities, 2000);
        $total = $cities === null ? 0 : count($cities);

        $cityClass = ModelResolver::cityClass();
        $stateClass = ModelResolver::stateClass();

        // ponytail: 150k+ rows — cache country/state lookups.
        $countryIds = ModelResolver::countryClass()::query()->pluck('id', 'iso2')->all();
        $stateIds = $stateClass::query()
            ->get(['id', 'country_id', 'code'])
            ->mapWithKeys(fn ($state) => [$state->country_id . '|' . $state->code => $state->id])
            ->all();

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $done = 0;
        $now = CarbonImmutable::now()->toDateTimeString();

        foreach ($chunks as $chunk) {
            // Scope the preload to this chunk's countries: same keys resolve,
            // fewer unrelated same-name rows cross country boundaries.
            $chunkCountryIds = [];

            foreach ($chunk as $row) {
                if (isset($row['country_code'], $countryIds[$row['country_code']])) {
                    $chunkCountryIds[] = $countryIds[$row['country_code']];
                }
            }

            $existingByKey = $cityClass::query()
                ->lockForUpdate()
                ->whereIn('country_id', array_values(array_unique($chunkCountryIds)))
                ->whereIn('name', array_column($chunk, 'name'))
                ->get(['id', 'country_id', 'state_id', 'name', 'country_code', 'state_code', 'latitude', 'longitude'])
                ->keyBy(fn ($city): string => $city->country_id . '|' . ($city->state_id ?? '') . '|' . $city->name);

            $inserts = [];

            foreach ($chunk as $row) {
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
                // ponytail: upstream city/state datasets drift (518 cities reference
                // codes absent from states.json) — keep the code string, null the link.
                $stateId = $stateCode !== null
                    ? ($stateIds[$countryId . '|' . $stateCode] ?? null)
                    : null;

                $attrs = [
                    'country_id' => $countryId,
                    'state_id' => $stateId,
                    'name' => $row['name'],
                    'country_code' => $row['country_code'] ?? null,
                    'state_code' => $stateCode,
                    'latitude' => is_numeric($row['latitude'] ?? null) ? (float) $row['latitude'] : null,
                    'longitude' => is_numeric($row['longitude'] ?? null) ? (float) $row['longitude'] : null,
                ];

                $key = $countryId . '|' . ($stateId ?? '') . '|' . $row['name'];
                $existing = $existingByKey->get($key);

                if ($existing === null) {
                    if (isset($inserts[$key])) {
                        $skipped++;

                        continue;
                    }

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
                } else {
                    $skipped++;
                }
            }

            if ($inserts !== []) {
                $cityClass::query()->insert(array_values($inserts));
            }

            $done += count($chunk);

            if ($progress !== null) {
                $progress('cities', $done, $total);
            }
        }

        if ($progress !== null && $total === 0) {
            $progress('cities', 0, 0);
        }

        return compact('created', 'updated', 'skipped');
    }
}
