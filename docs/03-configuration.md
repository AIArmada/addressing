---
title: Configuration
---

# Configuration

The package publishes a `config/addressing.php` file with these sections:

## Database

```php
'database' => [
],
```

JSON column type is controlled by `commerce_json_column_type('addressing', 'json')` and inherits from `COMMERCE_JSON_COLUMN_TYPE` when set.

## Tables

```php
'tables' => [
    'countries' => 'countries',
    'areas' => 'address_areas',
    'addresses' => 'addresses',
    'addressables' => 'addressables',
    'snapshots' => 'address_snapshots',
    'states' => 'states',
    'cities' => 'cities',
    'area_state_links' => 'address_area_state_links',
    'area_names' => 'address_area_names',
    'area_roles' => 'address_area_roles',
    'area_relationships' => 'address_area_relationships',
    'postal_codes' => 'postal_codes',
    'area_postal_codes' => 'address_area_postal_codes',
    'address_area_assignments' => 'address_area_assignments',
],
```

Override any table name via environment variables or config publishing.

- `states` and `cities` back the first-class `State` and `City` models
- `cities.state_id` is nullable; countries without a state/province level can still use country-scoped cities
- Addresses may store free-text `state` / `city` strings and optionally link via `state_id` / `city_id`

## Models and Geography Providers

```php
'models' => [
    'state' => AIArmada\Addressing\Models\State::class,
    'city' => AIArmada\Addressing\Models\City::class,
],
'geography' => [
    'providers' => [
        AIArmada\Addressing\Geography\Malaysia\MalaysiaGeographyProvider::class,
    ],
],
```

Providers define country address levels such as state, district, municipality or locality. Resolve the profile by country with `CountryAddressProfileResolver`; do not assume that `admin_area_1` means the same thing in every country.

Country-specific formatters are configured separately from geography providers:

```php
'formatters' => [
    AIArmada\Addressing\Geography\Malaysia\MalaysiaAddressFormatter::class,
],
```

`FormatAddressAction` resolves a formatter by `AddressData::countryCode` and falls back to the generic formatter when no country formatter is registered. This keeps formatting independent from geography seeding.

## Navigation Links

Navigation link columns (`google_maps_url`, `waze_url`, `navigation_links`) are part of the `addresses` and `address_snapshots` table schemas. They use the configured JSON column type for `navigation_links`.

Manual URLs always win over generated URLs. See `12-navigation-links.md` for full priority rules.

## Place metadata

Address areas store the place itself. Alternate names, address roles, typed
relationships and postcode coverage are stored separately. Alternate names
have a type such as `common` or `abbreviation`; there is no unused language
column because locale-aware name selection is not implemented.

## Defaults

```php
'defaults' => [
    'country_code' => env('ADDRESS_DEFAULT_COUNTRY_CODE'),
    'locale' => env('ADDRESS_DEFAULT_LOCALE'),
],
```

## Area Sources

```php
'area_sources' => [
    // App\Addressing\MalaysiaAddressAreaSource::class,
],
```

Register your `AddressAreaSource` implementations here. They become available to the `address:import-areas` command.
