<?php

declare(strict_types=1);

use AIArmada\Addressing\Geography\Malaysia\MalaysiaAddressFormatter;
use AIArmada\Addressing\Geography\Malaysia\MalaysiaGeographyProvider;
use AIArmada\Addressing\Models\City;
use AIArmada\Addressing\Models\State;

return [
    'database' => [
        'json_column_type' => env('ADDRESSING_JSON_COLUMN_TYPE', 'jsonb'),
    ],

    'tables' => [
        'countries' => 'countries',
        'areas' => 'address_areas',
        'addresses' => 'addresses',
        'addressables' => 'addressables',
        'snapshots' => 'address_snapshots',
        'states' => 'states',
        'cities' => 'cities',
        'country_currency_links' => 'country_currency_links',
        'country_timezone_links' => 'country_timezone_links',
        'area_state_links' => 'address_area_state_links',
        'area_city_links' => 'address_area_city_links',
        'area_names' => 'address_area_names',
        'area_roles' => 'address_area_roles',
        'area_relationships' => 'address_area_relationships',
        'postal_codes' => 'postal_codes',
        'area_postal_codes' => 'address_area_postal_codes',
        'address_area_assignments' => 'address_area_assignments',
    ],

    'models' => [
        'state' => State::class,
        'city' => City::class,
    ],

    'geography' => [
        // Add country providers here; the core package remains country-neutral.
        'providers' => [
            MalaysiaGeographyProvider::class,
        ],
    ],

    'formatters' => [
        MalaysiaAddressFormatter::class,
    ],

    'defaults' => [
        'country_code' => env('ADDRESS_DEFAULT_COUNTRY_CODE'),
        'locale' => env('ADDRESS_DEFAULT_LOCALE'),
    ],

    'area_sources' => [
        // App\Addressing\MalaysiaAddressAreaSource::class,
    ],
];
