---
title: Addressing Context
package: addressing
status: current
surface: domain
family: foundation
keywords:
  - address
  - addresses
  - country
  - state
  - city
  - postcode
  - area-import
  - snapshot
  - formatting
---

# Addressing Context

## Snapshot
- Composer: `aiarmada/addressing`
- Role: Canonical reusable-address domain: owner-scoped polymorphic addresses, global geography reference data, snapshots, formatting, and import pipeline.
- Triggers: address, addresses, country, state, city, postcode, area-import, snapshot
- Search first: `src/Models, src/Actions, src/Support, config, database/migrations, docs`
- Related: `commerce-support`, `filament-addressing`, `customers`, `orders`, `events`
- Paired: `filament-addressing` (Filament admin adapter)

## Read next
1. `docs/01-overview.md`
2. `docs/03-configuration.md`
3. `docs/04-usage.md`
4. `docs/99-troubleshooting.md`
5. `../filament-addressing/CONTEXT.md` when the change crosses UI/domain
6. `docs/02-installation.md` when setup or publishing changes are involved

## Guardrails
- Owns models, actions, services, events, calculations, and persistence rules.
- Canonical-addressing doctrine: addressing is required infrastructure for identity/addressing consumers — downstream `require` (not `suggest`) is the policy, starting with `customers`. Consumers inherit its migrations; dataset seeding stays opt-in.
- Direction guard (mechanical, see `tests/src/CommerceSupport/Architecture/CommerceSupportArchitectureTest.php`): addressing `require` stays at support + package-tools, and `src/` never imports consumer namespaces (`Customers`, `Persons`, `Orders`, `Events`). Never depend back.
- `Address` + `HasAddresses` is the forward path for new reusable attachments. The customers package is the first pilot consumer; its `customer_addresses` lineage remains frozen and is bridged with `toAddressingData()` without a backfill.
- Reference geography (`countries`, `states`, `cities`, areas, postcodes, and links) is global; instance addresses, pivots, and snapshots remain owner-scoped. Use `OwnerQuery` for raw queries against the instance tier.
- `AddressingTableResolver` is the sole table-name resolver for runtime readers and migrations; it reads `addressing.database.tables.*`.
- If admin UI changes too, audit `filament-addressing`.
- Update `docs/*.md` in the same pass when public behavior or config changes.

## Decide fast
- Use when: Storing, validating, formatting, attaching, or importing addresses / geographic areas.
- Skip when: Tenant/org identity — see organizations; contact points — see contacting.
- Owner/security: `Address`, `Addressable`, and `AddressSnapshot` are owner-scoped; geography reference data remains global. The attaching model and address must be resolved in the same owner context.

## Key surfaces
- Models: `Address`, `AddressArea`, `AddressAreaAssignment`, `AddressAreaCityLink`, `AddressAreaName`, `AddressAreaPostalCode`, `AddressAreaRelationship`, `AddressAreaRole`, `AddressAreaStateLink`, `AddressCountry`
- Actions/Services: `Actions/BuildAddressNavigationLinksAction`, `Actions/CreateAddressSnapshotAction`, `Actions/FormatAddressAction`, `Actions/ImportAddressAreasAction`, `Actions/ImportPostalCodesAction`, `Actions/NormalizeAddressDataAction`, `Actions/SaveAddressAreaAction`, `Actions/SearchAddressAreasAction`
- Config `addressing.php`: `database.tables`, `database.json_column_type`, `models`, `countries`, `areas`, `addresses`, `addressables`, `snapshots`, `states`, `cities`

## Docs map
- Start: `01-overview` → `03-configuration` → `04-usage` → `99-troubleshooting`
- Deep dives: `05-country-data.md`, `06-consuming-packages.md`, `07-adoption-levels.md`, `08-package-playbooks.md`, `09-migration-recipes.md`, `10-contracts-and-examples.md`, `11-agent-rollout-checklists.md`, `12-navigation-links.md`
