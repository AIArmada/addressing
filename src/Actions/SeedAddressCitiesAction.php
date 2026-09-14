<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\ModelResolver;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use RuntimeException;

class SeedAddressCitiesAction
{
    public function execute(?array $cities = null): array
    {
        if ($cities === null) {
            $path = __DIR__ . '/../../resources/data/cities.json';
            $cities = self::readJson($path);
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

        foreach (array_chunk($cities, 2000) as $chunk) {
            $existingByKey = $cityClass::query()
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

            if ($inserts !== []) {
                $cityClass::query()->insert(array_values($inserts));
            }
        }

        return compact('created', 'updated', 'skipped');
    }

    /**
     * @return array<int, mixed>
     */
    private static function readJson(string $path): array
    {
        $gzPath = $path . '.gz';

        if (file_exists($gzPath)) {
            $contents = gzfile($gzPath, false);

            if ($contents === false) {
                throw new RuntimeException("Unable to read gzip file: {$gzPath}");
            }

            return json_decode(implode('', $contents), true, 512, JSON_THROW_ON_ERROR);
        }

        if (! file_exists($path)) {
            throw new RuntimeException("Data file not found: {$path}");
        }

        return json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    }
}
