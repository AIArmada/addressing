<?php

declare(strict_types=1);

use AIArmada\Addressing\Geography\Malaysia\MalaysiaGeographyProvider;
use AIArmada\Addressing\Models\City;
use AIArmada\Addressing\Models\State;

return [
    'tables' => [
        'countries' => 'countries',
        'areas' => 'address_areas',
        'addresses' => 'addresses',
        'addressables' => 'addressables',
        'snapshots' => 'address_snapshots',
        'states' => 'states',
        'cities' => 'cities',
        'area_state_links' => 'address_area_state_links',
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

    'defaults' => [
        'country_code' => env('ADDRESS_DEFAULT_COUNTRY_CODE'),
        'locale' => env('ADDRESS_DEFAULT_LOCALE'),
    ],

    'area_sources' => [
        // App\Addressing\MalaysiaAddressAreaSource::class,
    ],
];
