<?php

declare(strict_types=1);

use AIArmada\Addressing\Geography\Malaysia\MalaysiaAddressFormatter;
use AIArmada\Addressing\Geography\Malaysia\MalaysiaGeographyProvider;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Models\City;
use AIArmada\Addressing\Models\State;
use AIArmada\Addressing\Support\AddressingTableResolver;

return [
    'database' => [
        'json_column_type' => env('ADDRESSING_JSON_COLUMN_TYPE', 'jsonb'),
        'tables' => AddressingTableResolver::defaults(),
    ],

    'models' => [
        'country' => AddressCountry::class,
        'state' => State::class,
        'city' => City::class,
    ],

    'features' => [
        'owner' => [
            'enabled' => env('ADDRESSING_OWNER_ENABLED', true),
            'include_global' => env('ADDRESSING_OWNER_INCLUDE_GLOBAL', false),
            'auto_assign_on_create' => env('ADDRESSING_OWNER_AUTO_ASSIGN', true),
        ],
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
