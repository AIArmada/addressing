---
title: Troubleshooting
---

# Troubleshooting

## Missing Countries After Migration

Run the seed command:

```bash
php artisan address:seed-countries
```

## Missing Malaysia States or Cities

`address:seed-countries` only seeds ISO countries. For structured Malaysia geography, run `app(SeedCountryGeographiesAction::class)->execute('MY')` after countries exist. This also imports the hierarchy and creates explicit State↔AddressArea links.

## Address Has Text State/City but No Relations

Free-text `state` / `city` columns do not automatically populate `state_id` / `city_id`. Set the foreign keys when you want `Address::state()` / `Address::city()` relations.

## Duplicate Source IDs During Area Import

The `address_areas` table has a unique constraint on `(source, source_id)`. If you encounter duplicate key errors, check that your `AddressAreaSource` does not yield duplicate `sourceId` values for the same `key()`.

## Missing Parent During Area Import

When importing areas with a parent hierarchy, ensure parents are imported before their children, or use a `parent_source_id` that already exists in the database. The import action resolves parents by `source + parent_source_id` lookup.

## JSON Column Type Issues

If you get JSON encoding errors, ensure your database supports the configured column type. For PostgreSQL:

```env
ADDRESS_JSON_COLUMN_TYPE=jsonb
```

For SQLite or MySQL:

```env
ADDRESS_JSON_COLUMN_TYPE=json
```

## Navigation URL Not Showing

Make sure the addressing migrations have run. Check the `google_maps_url` and `waze_url` columns exist on your `addresses` table.

If a manual URL is set but not appearing in the output, verify it passes `NormalizeNavigationUrl` validation — it must have an `http://` or `https://` scheme.

## Command Not Found

If `address:seed-countries` is not available, publish the vendor assets:

```bash
php artisan vendor:publish --provider="AIArmada\Addressing\AddressingServiceProvider"
```
