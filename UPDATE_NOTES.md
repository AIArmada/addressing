# Update Notes

Updated for country/territory wording and entity classification.

- `countries.php` record count: 249
- Added `entity_type` to every country/territory record.
- Added nullable `is_independent` to every country/territory record.
- Updated docs to say 249 ISO 3166-1 country/territory records, not 249 sovereign countries.
- Updated migration instructions for `address_countries`.
- Kept state/city/district/postcode data external through `AddressAreaSource`.

## 2026-06-13 — Tests moved to monorepo root

- All package tests moved from `packages/addressing/tests/` to `tests/src/Addressing/` (root monorepo convention).
- Removed `AIArmada\\Addressing\\Tests\\` autoload-dev entry from root `composer.json`.
- Tests now extend `AIArmada\Commerce\Tests\TestCase` via `tests/Pest.php` scoping.
- Delete `packages/addressing/tests/` locally if still present (`rm -rf`).

## 2026-06-13 — Navigation links addon

- Added `google_maps_url`, `waze_url`, `navigation_links` columns to `addresses` and `address_snapshots` tables (migration `2001_01_01_000006`).
- Updated `Address`, `AddressSnapshot` models with fillable and casts.
- Added `googleMapsUrl`, `wazeUrl`, `navigationLinks` to `AddressData` with aliases in `AddressAliasMap`.
- Added `NormalizeNavigationUrl` support class (validates HTTP(S) URLs, nulls empty/invalid).
- Added `BuildAddressNavigationLinksAction` with priority rules: manual > nav links > coords > formatted > null.
- Updated `CreateAddressSnapshotAction` to copy navigation links into snapshots.
- Registered action in `AddressingServiceProvider`.
- Added 31 new tests (65 total, 131 assertions). All passing.
- Added `docs/12-navigation-links.md` and updated `docs/04-usage.md`.
