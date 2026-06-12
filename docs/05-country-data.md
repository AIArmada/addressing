---
title: Country Data
---

# Country Data

## Bundled Dataset

The package bundles ISO 3166-1 country/territory data only. This is the only bundled reference dataset.

File location: `resources/data/countries.php`

The dataset contains **249 records** — these are ISO 3166-1 address entities, not 249 sovereign countries. Records include:

- ISO2, ISO3, numeric codes
- Entity type (country, territory, dependency, constituent_country, associated_state, special_area, disputed_or_observer)
- Independence flag (nullable — for display/filtering only, not address validity)
- Names (official, common, native)
- Phone codes and calling codes
- Capital (name and coordinates)
- Country centroid coordinates
- Region and subregion
- Currency codes
- Language codes
- Timezones
- Top-level domains
- Extended metadata (demonyms, area, population, borders, translations)

## What is NOT Bundled

The following must be supplied by users through `AddressAreaSource`, array imports, or CSV imports:

- States, federal territories, provinces, prefectures, emirates
- Districts, cities, towns, villages, mukim, neighbourhoods
- Postcodes

## Audit Process

Before a PR is finalized, the country data should be audited against `nnjeim/world`:

```bash
composer require nnjeim/world --dev
php artisan address:audit-countries --against=vendor/nnjeim/world/resources/json/countries.json
```

If the audit command is not yet implemented, compare manually. Record any intentional differences here.

### Intentional Differences from nnjeim/world

| Field | aiarmada/addressing | nnjeim/world | Reason |
|-------|-------------------|--------------|--------|
| — | — | — | (None yet — first audit pending) |

## Seed Command

```bash
php artisan address:seed-countries
```

This is idempotent — running it multiple times is safe.
