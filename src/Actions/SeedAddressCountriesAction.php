<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Models\AddressCountry;
use RuntimeException;

class SeedAddressCountriesAction
{
    public function execute(): array
    {
        $path = __DIR__ . '/../../resources/data/countries.json';

        $raw = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($raw)) {
            throw new RuntimeException('Country data file must contain a JSON array.');
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($raw as $row) {
            if (! isset($row['iso2'], $row['name'])) {
                $skipped++;

                continue;
            }

            $existing = AddressCountry::where('iso2', $row['iso2'])->first();

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

            if ($existing === null) {
                AddressCountry::create($attrs);
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
