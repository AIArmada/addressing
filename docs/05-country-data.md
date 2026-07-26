---
title: Country Data
---

# Country Data

## Bundled Dataset

The package always bundles ISO 3166-1 country/territory data.

File location: `resources/data/countries.json`

The bundled `MalaysiaGeographyProvider` supplies Malaysia's State/City catalogs, address-level definitions, AddressArea hierarchy, and explicit State↔AddressArea mappings. Area-to-city relationships use the separate `address_area_city_links` pivot when a provider needs to associate an area directly with a canonical city. It is selected with `SeedCountryGeographiesAction::execute('MY')` after countries are seeded.

The dataset contains **250 records** — these are ISO 3166-1 address entities, not 250 sovereign countries. Records include:

- ISO2, ISO3, numeric codes
- Names (common, native)
- Phone codes
- Capital
- Country centroid coordinates
- Region and subregion
- Currency code
- Timezones
- Top-level domain
- Translated country names

Currency, language, and timezone reference data is owned and seeded by `commerce-support`:

- `currencies` from the shared currency catalogue
- `languages` from the shared ISO 639-1 catalogue
- `timezones` from the shared IANA timezone catalogue

Countries are linked to shared currencies and timezones through UUID pivot tables. The country table does not store currency or timezone JSON/scalar columns. Country-language relationships are intentionally not modelled until a trusted mapping dataset is available.

## What is NOT Bundled by Default

Without selecting a country provider, the following must be supplied by users through `State`/`City` models, `AddressAreaSource`, array imports, or CSV imports:

- States, federal territories, provinces, prefectures, emirates
- Districts, cities, towns, villages, mukim, neighbourhoods
- Postcodes and worldwide area hierarchies

## Bundled States and Cities

`states.json` and `cities.json` are bundled from the same nnjeim/world source. Seed the global files first; country providers then complement those rows using stable country-scoped identities. The Malaysia provider updates matching states in place and adds Malaysia-specific rows not present in the global file, such as Putrajaya. It does not seed shared commerce-support reference data. The bundled area source contains no canonical city mapping data.

## Seed Command

```bash
php artisan address:seed-countries
php artisan commerce:seed-currencies
php artisan commerce:seed-languages
php artisan commerce:seed-timezones
php artisan address:seed-country-references
```

This is idempotent — running it multiple times is safe.
