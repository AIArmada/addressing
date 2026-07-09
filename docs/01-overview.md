---
title: Overview
---

# Addressing Package Overview

The `aiarmada/addressing` package provides a reusable address handling system for the AIArmada Commerce monorepo. It includes normalized address value objects, country reference data, generic administrative area schema, polymorphic address attachment, historical snapshots, and formatting/normalization utilities.

## Features

- **AddressData** — canonical address value object with alias normalization
- **AddressCountry** — ISO 3166-1 country/territory reference data (bundled)
- **State** / **City** — first-class geographic models with optional links from `Address` via `state_id` / `city_id`
- **AddressArea** — generic state/province/city/district/locality schema for broader area import
- **Address** — Eloquent model for persisted addresses
- **HasAddresses** — polymorphic trait for attaching addresses to any model
- **AddressSnapshot** — immutable point-in-time address snapshots
- **Formatting & Normalization** — contracts and default implementations
- **Area Import Pipeline** — import administrative areas via `AddressAreaSource`, arrays, or CSV
- **Malaysia geography seeder** — optional MY states and cities for `State` / `City` tables

## Package Layout

```
config/addressing.php          Package configuration
database/migrations/           Table migrations
database/seeders/              Country and geography seeders
src/Actions/                   Action classes
src/Casts/                     Custom Eloquent casts
src/Commands/                  Artisan commands
src/Contracts/                 Interfaces
src/Data/                      Value objects (DTOs)
src/Models/                    Eloquent models
src/Support/                   Support classes
src/Traits/                    Reusable traits
resources/data/countries.php   Bundled ISO 3166-1 data
docs/                          Package documentation
```

## Non-goals (v1)

- Tenant ownership of persisted addresses
- Geocoding providers
- Postcode validation by country
- Full UPU S42 formatting engine
- Worldwide bundled district/postcode datasets (beyond optional MY state/city seed data)
