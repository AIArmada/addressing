<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Models\AddressCountry;
use RuntimeException;

class SeedAddressCountriesAction
{
    /**
     * @param  ?callable(string, int, int, ?string=): void  $progress
     */
    public function execute(?callable $progress = null): array
    {
        $path = __DIR__ . '/../../resources/data/countries.json';

        $raw = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($raw)) {
            throw new RuntimeException('Country data file must contain a JSON array.');
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $total = count($raw);

        // One query replaces a SELECT per row.
        $existingByIso2 = AddressCountry::query()->get()->keyBy('iso2');

        foreach ($raw as $index => $row) {
            if ($progress !== null) {
                $progress('countries', $index + 1, $total);
            }

            if (! isset($row['iso2'], $row['name'])) {
                $skipped++;

                continue;
            }

            $attrs = [
                'iso2' => $row['iso2'],
                'name' => $row['name'],
                'phone_code' => $row['phone_code'] ?? null,
                'iso3' => $row['iso3'] ?? null,
                'numeric_code' => $row['numeric_code'] ?? null,
                'native' => $row['native'] ?? null,
                'capital' => $row['capital'] ?? null,
                'region' => $row['region'] ?? null,
                'subregion' => $row['subregion'] ?? null,
                'tld' => $row['tld'] ?? null,
                'latitude' => is_numeric($row['latitude'] ?? null) ? (float) $row['latitude'] : null,
                'longitude' => is_numeric($row['longitude'] ?? null) ? (float) $row['longitude'] : null,
                'emoji' => $row['emoji'] ?? null,
                'emojiU' => $row['emojiU'] ?? null,
                'translations' => $row['translations'] ?? null,
            ];

            $existing = $existingByIso2->get($row['iso2']);

            if ($existing === null) {
                $country = new AddressCountry;
                $country->fill($attrs);

                if (! $country->save()) {
                    $skipped++;

                    continue;
                }

                $existingByIso2->put($row['iso2'], $country);
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

        if ($progress !== null && $total === 0) {
            $progress('countries', 0, 0);
        }

        return compact('created', 'updated', 'skipped');
    }
}
