<?php

declare(strict_types=1);

return [
    'database' => [
        'json_column_type' => env('ADDRESS_JSON_COLUMN_TYPE', env('COMMERCE_JSON_COLUMN_TYPE', 'jsonb')),
    ],

    'tables' => [
        'countries' => 'address_countries',
        'areas' => 'address_areas',
        'addresses' => 'addresses',
        'addressables' => 'addressables',
        'snapshots' => 'address_snapshots',
    ],

    'defaults' => [
        'country_code' => env('ADDRESS_DEFAULT_COUNTRY_CODE', 'MY'),
        'locale' => env('ADDRESS_DEFAULT_LOCALE', 'ms-MY'),
    ],

    'area_sources' => [
        // App\Addressing\MalaysiaAddressAreaSource::class,
    ],
];
