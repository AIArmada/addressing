<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use LogicException;

final class AddressingTableResolver
{
    /**
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        return [
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
        ];
    }

    public static function resolve(string $key): string
    {
        $table = config("addressing.database.tables.{$key}", self::defaults()[$key] ?? null);

        if (! is_string($table) || mb_trim($table) === '') {
            throw new LogicException(sprintf(
                'Addressing table [%s] must be configured at [addressing.database.tables.%s].',
                $key,
                $key,
            ));
        }

        return $table;
    }
}
