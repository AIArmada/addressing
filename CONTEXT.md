---
title: Addressing Context
package: addressing
status: planned
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
- Role: Reusable address domain: polymorphic addresses, country/area/state/city/postcode reference data, snapshots, formatting, and import pipeline.
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
- If admin UI changes too, audit `filament-addressing`.
- Update `docs/*.md` in the same pass when public behavior or config changes.

## Decide fast
- Use when: Storing, validating, formatting, or importing addresses / geographic areas.
- Skip when: Tenant scoping (explicit non-goal in v1) or org identity — see organizations.
- Owner/security: No owner scoping by design (v1); shared reference data.

## Key surfaces
- Models: `Address`, `AddressArea`, `AddressAreaAssignment`, `AddressAreaCityLink`, `AddressAreaName`, `AddressAreaPostalCode`, `AddressAreaRelationship`, `AddressAreaRole`, `AddressAreaStateLink`, `AddressCountry`
- Actions/Services: `Actions/BuildAddressNavigationLinksAction`, `Actions/CreateAddressSnapshotAction`, `Actions/FormatAddressAction`, `Actions/ImportAddressAreasAction`, `Actions/ImportPostalCodesAction`, `Actions/NormalizeAddressDataAction`, `Actions/SaveAddressAreaAction`, `Actions/SearchAddressAreasAction`
- Config `addressing.php`: `database`, `json_column_type`, `tables`, `countries`, `areas`, `addresses`, `addressables`, `snapshots`, `states`, `cities`

## Docs map
- Start: `01-overview` → `03-configuration` → `04-usage` → `99-troubleshooting`
- Deep dives: `05-country-data.md`, `06-consuming-packages.md`, `07-adoption-levels.md`, `08-package-playbooks.md`, `09-migration-recipes.md`, `10-contracts-and-examples.md`, `11-agent-rollout-checklists.md`, `12-navigation-links.md`
