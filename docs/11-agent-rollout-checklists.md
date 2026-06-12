---
title: Agent Rollout Checklists
---

# Agent Rollout Checklists

## Purpose

This document splits addressing adoption work across agents without overlapping file ownership.

Agents must not edit files owned by another active agent unless explicitly reassigned.

## Global rules for all agents

Before editing:

- Read the target package `CONTEXT.md`.
- Read `packages/addressing/CONTEXT.md`.
- Read addressing docs `06` to `10`.
- Identify adoption level.
- Write a brief plan.
- Add or update tests.
- Update package docs if behavior changes.
- Run only affected package checks.

Do not:

- Delete legacy columns in first-pass adoption.
- Add database constraints or cascades.
- Run repo-wide Pint/PHPStan/Pest.
- Put package-specific rules into core addressing.
- Treat Filament UI scoping as security.

## Agent A: Addressing public API auditor

### Owns

```txt
packages/addressing/src/Data
packages/addressing/src/Casts
packages/addressing/src/Actions
packages/addressing/src/Services
packages/addressing/docs
packages/addressing/tests
```

### Tasks

- Verify `AddressData` supports known aliases.
- Verify `AddressDataCast` works for JSON columns.
- Verify formatter accepts `AddressData`, `Address`, and snapshots if intended.
- Verify snapshot action exists and does not mutate source addresses.
- Verify docs explain adoption levels.

### Must not edit

- consuming package models or migrations
- provider package mappers
- commerce-support contracts unless assigned

### Checks

```bash
./vendor/bin/pest --parallel packages/addressing/tests
./vendor/bin/phpstan analyse packages/addressing/src --level=6
```

## Agent B: Commerce-support contracts

### Owns

```txt
packages/commerce-support/src/Contracts
packages/commerce-support/docs
packages/commerce-support/tests
```

### Tasks

- Decide whether commerce-support hard-requires addressing or uses optional interfaces.
- Add `HasBillingAddress` / `HasShippingAddress` contracts only if approved.
- Keep contracts minimal.
- Do not add storage.

### Must not edit

- customers storage migration
- orders snapshots
- provider package mappers

### Checks

```bash
./vendor/bin/pest --parallel packages/commerce-support/tests
./vendor/bin/phpstan analyse packages/commerce-support/src --level=6
```

## Agent C: Customers package

### Owns

```txt
packages/customers/src
packages/customers/database
packages/customers/docs
packages/customers/tests
```

### Tasks

- Add `HasAddresses` to reusable customer address owner model if approved.
- Add conversion methods from legacy `customer_addresses` to `AddressData`.
- Create data-copy migration/action only in migration phase.
- Preserve billing/shipping/default semantics.
- Do not delete `customer_addresses` in first pass.

### Tests

- Customer can create shipping address.
- Customer can create billing address.
- Primary address remains primary per type.
- Legacy rows convert correctly to `AddressData`.
- Owner scoping remains enforced if package is tenant-owned.

## Agent D: Orders package

### Owns

```txt
packages/orders/src
packages/orders/database
packages/orders/docs
packages/orders/tests
```

### Tasks

- Adopt `AddressData` for order address input/output.
- Use snapshots for billing/shipping addresses.
- Do not link order address only to mutable customer address.
- Keep or migrate `order_addresses` intentionally.

### Tests

- Order snapshot is unchanged after customer address update.
- Billing and shipping addresses can differ.
- Existing order address data maps to canonical shape.

## Agent E: Events / venues / institutions

### Owns

```txt
packages/events/src
packages/events/database
packages/events/docs
packages/events/tests
packages/institutions/src if assigned
packages/venues/src if assigned
```

### Tasks

- Use `HasAddresses` for reusable venue/institution addresses.
- Add event address resolver in events package.
- Create event location snapshot on publish/approval if required.
- Preserve manual location text fallback.
- Do not put event fallback logic inside core addressing.

### Tests

- Event uses venue address when present.
- Event falls back to institution/masjid address when venue missing.
- Event snapshot does not change after venue address update.
- Manual/online events do not crash.

## Agent F: Shipping package

### Owns

```txt
packages/shipping/src
packages/shipping/docs
packages/shipping/tests
```

### Tasks

- Cast `origin_address` and `destination_address` to `AddressData`.
- Ensure serialized JSON remains compatible with existing payloads.
- Do not migrate JSON columns to shared addresses.

### Tests

- Origin address casts to `AddressData`.
- Destination address casts to `AddressData`.
- JSON serialization is stable.

## Agent G: Provider adapters

### Owns

```txt
packages/chip/src
packages/jnt/src
packages/cashier/src
packages/cashier-chip/src
```

Only one provider package per agent if multiple people are active.

### Tasks

- Add provider-specific mapper from/to `AddressData`.
- Keep provider field names at the boundary.
- Do not migrate provider storage unless separately approved.
- Do not change provider API semantics.

### Tests

- Mapper converts canonical address to provider payload.
- Mapper handles null optional fields.
- Mapper maps postcode/zip/postCode correctly.

## Agent H: Tax package

### Owns

```txt
packages/tax/src
packages/tax/docs
packages/tax/tests
```

### Tasks

- Normalize shipping/billing/address context keys to partial `AddressData`.
- Do not require full line1/line2.
- Do not create address tables.

### Tests

- Shipping address takes precedence if business rule says so.
- Billing fallback works.
- Country/state/postcode extraction works.

## Agent I: Docs/config packages

### Owns

```txt
packages/docs/src
packages/docs/config
packages/docs/docs
packages/cashier-chip/config if assigned
```

### Tasks

- Preserve string address config support.
- Add optional structured address array support only if useful.
- Do not require database storage.

### Tests

- Existing string config still renders.
- Structured address config renders when present.

## Agent J: QA / auditor

### Owns

No implementation files unless fixing assigned test failures.

### Tasks

- Review package-level adoption decisions.
- Verify no historical address uses mutable-only storage.
- Verify no DB constraints/cascades were added.
- Verify tenant-owned models remain owner-scoped.
- Verify docs are updated.
- Verify package-scoped tests passed.

### Grep checks

```bash
rg -n -- "constrained\(|cascadeOnDelete\(" packages/*/database
rg -n -- "address_line_1|street_address|zip_code|postCode|postal_code" packages/*/src packages/*/database
rg -n -- "AddressData|HasAddresses|AddressSnapshot" packages/*/src packages/*/docs
```

## Rollout board suggestion

Create one issue per package or surface:

```txt
addressing-adoption: customers
addressing-adoption: orders
addressing-adoption: events-venues
addressing-adoption: shipping
addressing-adoption: provider-mappers
addressing-adoption: tax
addressing-adoption: docs-config
```

Each issue must state:

- target adoption level
- files likely touched
- dependency decision
- migration needed or not
- test expectations
- docs expectations

## Done definition

A package adoption task is done when:

- Adoption level is documented.
- Code uses the appropriate addressing primitive.
- Existing behavior is covered by tests.
- New behavior is covered by tests.
- Package docs are updated.
- Package-scoped checks pass.
- No unrelated cleanup was included.
