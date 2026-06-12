---
title: Addressing Consumer Adoption Documentation Pack
---

# Addressing Consumer Adoption Documentation Pack

This pack documents how other `aiarmada/*` and `majlisilmu/*` packages should adopt `aiarmada/addressing`.

It is intentionally separate from the core addressing implementation instruction. The core package explains how to build `aiarmada/addressing`; this pack explains how the rest of the monorepo should consume it.

## Files

- `ADDRESSING_CONSUMER_ADOPTION_IMPLEMENTATION.md` — master instruction for AI agents implementing adoption across packages.
- `packages/addressing/docs/06-consuming-packages.md` — main conceptual guide: why, what, who, when, and how.
- `packages/addressing/docs/07-adoption-levels.md` — exact levels of adoption from DTO-only to storage migration.
- `packages/addressing/docs/08-package-playbooks.md` — package-by-package recommendations.
- `packages/addressing/docs/09-migration-recipes.md` — migration and data-copy recipes.
- `packages/addressing/docs/10-contracts-and-examples.md` — copy-paste examples for DTOs, casts, snapshots, mappers, and traits.
- `packages/addressing/docs/11-agent-rollout-checklists.md` — non-overlapping multi-agent checklists.
- `UPDATE_NOTES.md` — summary of what this pack adds.

## Core position

Using `aiarmada/addressing` does **not** automatically mean deleting every existing address column or table.

It means every package should use the shared address language where appropriate:

- `AddressData` for normalized address values.
- `AddressDataCast` for JSON columns.
- `Address` + `HasAddresses` for reusable saved addresses.
- `AddressSnapshot` or address snapshot JSON for historical records.
- Provider mappers for gateway/logistics APIs.
- Country/area resolvers for country and locality normalization.

The storage migration decision must be made package-by-package.
