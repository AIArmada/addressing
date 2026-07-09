---
title: Consuming Packages
---

# Consuming Packages

## Purpose

This document explains how other packages should use `aiarmada/addressing`.

It answers:

- Why should other packages use addressing?
- What does using addressing mean?
- Who should use it?
- When should a package adopt it?
- How should adoption happen?
- Should existing address tables and columns be removed?

## The short answer

Using `aiarmada/addressing` means using one shared address language across the monorepo.

It does **not** automatically mean every package must delete existing address columns or point everything to the `addresses` table.

Use the package in the right mode:

```txt
Reusable address       -> Address model + HasAddresses
Historical address     -> AddressSnapshot or AddressData JSON
Provider payload       -> AddressData mapper
Config display address -> string or optional AddressData array
IP/resolved location   -> ResolvedLocationData, not full AddressData
```

## Why use addressing?

Packages currently use different names for the same concepts:

```txt
line1
address_line_1
street_address
shipping_street_address
postal_code
postcode
zip_code
postCode
country
countryCode
resolved_country_code
```

This creates duplication and bugs:

- Different packages format addresses differently.
- Gateways receive inconsistent country/postcode fields.
- Orders may accidentally point to mutable customer addresses.
- Events may duplicate venue/institution address logic.
- Shipping providers need repeated mappers.
- Tax packages only need country/state/postcode but still receive inconsistent keys.
- Docs/config packages have no common way to support structured addresses.

`aiarmada/addressing` should become the shared contract so packages stop inventing new address shapes.

## What does using addressing mean?

It can mean any of these:

1. Accepting or returning `AddressData`.
2. Casting a JSON column to `AddressData`.
3. Formatting an address using `FormatAddressAction`.
4. Normalizing legacy/provider names using `AddressNormalizer`.
5. Mapping `AddressData` to a provider payload.
6. Storing a reusable address in `addresses` and linking with `addressables`.
7. Creating an immutable `AddressSnapshot`.
8. Looking up `address_countries` by ISO2 country code.
9. Resolving optional `address_areas` for state/city/district relationships.

It does not always mean storage migration.

## Who should use addressing?

### Strong consumers

These packages should normally require or strongly depend on `aiarmada/addressing`:

- customers
- orders
- events / venues / institutions
- shipping
- cashier
- commerce-support if shared address contracts live there

### Adapter consumers

These packages should usually use `AddressData` mappers:

- chip
- jnt
- cashier-chip
- other gateway or logistics adapters

### Light consumers

These packages may support addressing optionally:

- docs
- config-only vendor/company address packages
- signals / IP-location packages

## When should a package adopt addressing?

Adopt addressing when a package does any of the following:

- stores address fields
- snapshots address fields
- sends address payloads to providers
- formats addresses for display/PDF/email/API
- validates country/state/postcode
- resolves country names/codes
- supports billing/shipping/venue/company addresses

Do not adopt it only because a package has a `country` column unrelated to an address. For example, analytics, localization, or content targeting may need country metadata but not full address behavior.

## Should existing tables and columns be removed?

Not immediately.

Use this rule:

```txt
Adopt the contract first.
Migrate storage second.
Remove legacy columns last.
```

Do not delete existing columns in the first adoption pass.

Safe adoption order:

1. Add `AddressData` conversion methods.
2. Add mappers and casts.
3. Update write paths to produce `AddressData`.
4. Update read paths to consume `AddressData`.
5. Copy data into new storage only if needed.
6. Add tests proving old and new behavior match.
7. Remove legacy storage in a dedicated cleanup migration.

## Storage rule

Use shared `addresses` storage only for reusable, mutable addresses.

Examples:

```txt
Customer saved address
Institution address
Masjid address
Venue address
Branch address
```

Do not use shared mutable storage as the only source of truth for historical addresses.

Examples that must snapshot:

```txt
Order shipping address
Order billing address
Shipment destination address
Event published location
Tax calculation address
Gateway transaction address
```

## Country and area rule

Country should be stored and communicated using ISO2 country code where possible:

```php
'countryCode' => 'MY'
```

Display name may be resolved from the countries table (default `countries`):

```txt
MY -> Malaysia
```

State/city/district data should remain flexible. Not every country has states. Prefer free-text `state` / `city` fields for compatibility, optionally link structured `state_id` / `city_id` when first-class geography is available, and use `address_areas` for broader area import hierarchies.

## Package responsibility boundary

`aiarmada/addressing` owns:

- address DTOs
- address normalization
- address formatting
- address storage primitives
- address snapshots
- country records
- area import contracts
- reusable `HasAddresses` trait

Consuming packages own:

- when an address is required
- billing/shipping/venue/order semantics
- provider payload requirements
- event location fallback rules
- tax jurisdiction logic
- customer address UX
- package-specific migrations and tests

## Common mistakes

### Mistake: everything points to `addresses`

Bad:

```txt
orders.shipping_address_id -> addresses.id
```

If the customer updates the address later, historical order data changes accidentally.

Better:

```txt
orders -> address snapshot
```

### Mistake: provider package owns address rules

Bad:

```txt
chip package defines global address normalization rules
```

Better:

```txt
chip package maps AddressData to Chip payload only
```

### Mistake: IP location becomes address

Bad:

```txt
signal_sessions resolved city is treated as a postal address
```

Better:

```txt
ResolvedLocationData for approximate IP/location data
```

## Suggested docs updates in consumer packages

When a package adopts addressing, add or update:

- `docs/01-overview.md` with the address ownership model.
- `docs/03-configuration.md` if config keys are added.
- `docs/04-usage.md` with copy-paste examples.
- `docs/99-troubleshooting.md` for migration/normalization issues.

## Final principle

Use addressing as the shared language first. Use shared storage only when the address itself is reusable.

Architecture bagus bukan bermaksud semua benda masuk satu table. Kalau semua masuk satu table, itu bukan shared domain — itu stor bawah tangga. 
