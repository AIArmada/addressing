<?php

declare(strict_types=1);

return [
    'database' => [
        'json_column_type' => env('ADDRESS_JSON_COLUMN_TYPE', env('COMMERCE_JSON_COLUMN_TYPE', 'jsonb')),
    ],

    'owner' => [
        'enabled' => env('ADDRESSING_OWNER_ENABLED', false),
        'include_global' => env('ADDRESSING_OWNER_INCLUDE_GLOBAL', false),
        'auto_assign_on_create' => env('ADDRESSING_OWNER_AUTO_ASSIGN', true),
    ],

    'tables' => [
        'countries' => 'countries',
        'areas' => 'address_areas',
        'addresses' => 'addresses',
        'addressables' => 'addressables',
        'snapshots' => 'address_snapshots',
        'states' => 'states',
        'cities' => 'cities',
    ],

    'area_sources' => [
        // App\Addressing\MalaysiaAddressAreaSource::class,
    ],
];
