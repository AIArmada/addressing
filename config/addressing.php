<?php

declare(strict_types=1);

return [
    'tables' => [
        'countries' => 'countries',
        'areas' => 'address_areas',
        'addresses' => 'addresses',
        'addressables' => 'addressables',
        'snapshots' => 'address_snapshots',
        'states' => 'states',
        'cities' => 'cities',
    ],

    'defaults' => [
        'country_code' => env('ADDRESS_DEFAULT_COUNTRY_CODE', 'MY'),
        'locale' => env('ADDRESS_DEFAULT_LOCALE', 'ms-MY'),
    ],

    'area_sources' => [
        // App\Addressing\MalaysiaAddressAreaSource::class,
    ],
];
