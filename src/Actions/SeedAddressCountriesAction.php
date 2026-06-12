<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Models\AddressCountry;
use RuntimeException;

class SeedAddressCountriesAction
{
    public function execute(): array
    {
        $countries = require __DIR__ . '/../../resources/data/countries.php';

        if (! is_array($countries)) {
            throw new RuntimeException('Country data file must return an array.');
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($countries as $row) {
            if (! isset($row['iso2'], $row['iso3'], $row['name'])) {
                $skipped++;

                continue;
            }

            $existing = AddressCountry::where('iso2', $row['iso2'])->first();

            if ($existing === null) {
                AddressCountry::create([
                    'iso2' => $row['iso2'],
                    'iso3' => $row['iso3'],
                    'numeric_code' => $row['numeric_code'] ?? null,
                    'entity_type' => $row['entity_type'] ?? 'country',
                    'is_independent' => $row['is_independent'] ?? null,
                    'name' => $row['name'],
                    'official_name' => $row['official_name'] ?? null,
                    'common_name' => $row['common_name'] ?? null,
                    'native_name' => $row['native_name'] ?? null,
                    'emoji' => $row['emoji'] ?? null,
                    'phone_code' => $row['phone_code'] ?? null,
                    'calling_codes' => $row['calling_codes'] ?? null,
                    'capital' => $row['capital'] ?? null,
                    'capital_latitude' => $row['capital_latitude'] ?? null,
                    'capital_longitude' => $row['capital_longitude'] ?? null,
                    'latitude' => $row['latitude'] ?? null,
                    'longitude' => $row['longitude'] ?? null,
                    'region' => $row['region'] ?? null,
                    'subregion' => $row['subregion'] ?? null,
                    'currency_codes' => $row['currency_codes'] ?? null,
                    'default_currency_code' => $row['default_currency_code'] ?? null,
                    'language_codes' => $row['language_codes'] ?? null,
                    'timezones' => $row['timezones'] ?? null,
                    'top_level_domains' => $row['top_level_domains'] ?? null,
                    'metadata' => $row['metadata'] ?? null,
                ]);
                $created++;
            } else {
                $existing->fill([
                    'iso3' => $row['iso3'],
                    'numeric_code' => $row['numeric_code'] ?? null,
                    'entity_type' => $row['entity_type'] ?? 'country',
                    'is_independent' => $row['is_independent'] ?? null,
                    'name' => $row['name'],
                    'official_name' => $row['official_name'] ?? null,
                    'common_name' => $row['common_name'] ?? null,
                    'native_name' => $row['native_name'] ?? null,
                    'emoji' => $row['emoji'] ?? null,
                    'phone_code' => $row['phone_code'] ?? null,
                    'calling_codes' => $row['calling_codes'] ?? null,
                    'capital' => $row['capital'] ?? null,
                    'capital_latitude' => $row['capital_latitude'] ?? null,
                    'capital_longitude' => $row['capital_longitude'] ?? null,
                    'latitude' => $row['latitude'] ?? null,
                    'longitude' => $row['longitude'] ?? null,
                    'region' => $row['region'] ?? null,
                    'subregion' => $row['subregion'] ?? null,
                    'currency_codes' => $row['currency_codes'] ?? null,
                    'default_currency_code' => $row['default_currency_code'] ?? null,
                    'language_codes' => $row['language_codes'] ?? null,
                    'timezones' => $row['timezones'] ?? null,
                    'top_level_domains' => $row['top_level_domains'] ?? null,
                    'metadata' => $row['metadata'] ?? null,
                ]);

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
