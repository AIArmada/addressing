---
title: Addressing Package Context
package: aiarmada/addressing
status: planned
surface: package
family: core
---

## Snapshot

Composer package: `aiarmada/addressing`.

Role: reusable address domain package for address data objects, country reference data, area import contracts, address storage, addressable relationships, formatting, normalization and snapshots.

Start searches in:

- `packages/addressing/src`
- `packages/addressing/config`
- `packages/addressing/database`
- `packages/addressing/docs`
- `packages/addressing/resources/data`
- sibling package contexts for consumers such as customers, orders, events, shipping, cashier, tax and commerce-support

Related packages:

- `commerce-support` for owner scoping and shared primitives.
- Future `filament-addressing` for admin UI only.
- Optional future `addressing-world` adapter for `nnjeim/world` import/compare.

## Read next

- `docs/01-overview.md`
- `docs/03-configuration.md`
- `docs/04-usage.md`
- `docs/05-country-data.md`
- `docs/99-troubleshooting.md`
- `docs/02-installation.md`

## Guardrails

This package owns address primitives, country reference data, generic area schema, import contracts, address relationships and snapshots.

This package does not own venues, spaces, institutions, events, shipping providers, payment gateways, tax engines, documents or Filament UI.

Country data is the only bundled dataset. State, province, federal territory, district, city, mukim, village, neighbourhood and postcode data must be supplied by users through `AddressAreaSource`, array import or CSV import.

Use `address_` table prefix for package reference/snapshot tables. Use canonical developer fields `line1`, `line2`, `city`, `district`, `state`, `postcode`, `country`, and ISO2 `country_code`.

Do not add database constraints or cascades. Use UUID primary keys. Use package config for table names and JSON column type. Do not use soft deletes.

If owner scoping is enabled later, use `commerce-support` primitives. Filament is an adapter package, not a domain owner.

When public behavior, config, import contract, data policy or command usage changes, update docs in the same pass.
