---
title: Configuration
---

# Configuration

The package publishes a `config/addressing.php` file with these sections:

## Database

```php
'database' => [
    'json_column_type' => env('ADDRESS_JSON_COLUMN_TYPE', 'json'),
],
```

Set `ADDRESS_JSON_COLUMN_TYPE=jsonb` in your `.env` for PostgreSQL JSONB support.

## Tables

```php
'tables' => [
    'countries' => 'address_countries',
    'areas' => 'address_areas',
    'addresses' => 'addresses',
    'addressables' => 'addressables',
    'snapshots' => 'address_snapshots',
],
```

Override any table name via environment variables or config publishing.

## Navigation Links

Navigation link columns (`google_maps_url`, `waze_url`, `navigation_links`) are part of the `addresses` and `address_snapshots` table schemas (migrations `2001_01_01_000003` and `2001_01_01_000005`). They use the configured JSON column type for `navigation_links`.

Manual URLs always win over generated URLs. See `12-navigation-links.md` for full priority rules.

## Defaults

```php
'defaults' => [
    'country_code' => env('ADDRESS_DEFAULT_COUNTRY_CODE', 'MY'),
    'locale' => env('ADDRESS_DEFAULT_LOCALE', 'ms-MY'),
],
```

## Area Sources

```php
'area_sources' => [
    // App\Addressing\MalaysiaAddressAreaSource::class,
],
```

Register your `AddressAreaSource` implementations here. They become available to the `address:import-areas` command.
