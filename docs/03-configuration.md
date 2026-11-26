---
title: Configuration
---

# Configuration

The package publishes a `config/addressing.php` file with these sections:

## Database

```php
'database' => [
    'json_column_type' => env('ADDRESSING_JSON_COLUMN_TYPE', 'jsonb'),
    'tables' => AddressingTableResolver::defaults(),
],
```

`addressing.database.tables.*` is the only table-name configuration source.
Runtime models, integrations, and migrations resolve names through
`AddressingTableResolver`; configure this map (via the published config file)
before deploying. Table names have no `env()` overrides. The map configures
the default physical table; per-instance names (e.g. self-join aliases) take
precedence when set.

The default map is:

```php
[
    'countries' => 'countries',
    'areas' => 'address_areas',
    'addresses' => 'addresses',
    'addressables' => 'addressables',
    'snapshots' => 'address_snapshots',
    'states' => 'states',
    'cities' => 'cities',
    'country_currency_links' => 'country_currency_links',
    'country_timezone_links' => 'country_timezone_links',
    'area_state_links' => 'address_area_state_links',
    'area_names' => 'address_area_names',
    'area_roles' => 'address_area_roles',
    'area_relationships' => 'address_area_relationships',
    'postal_codes' => 'postal_codes',
    'area_postal_codes' => 'address_area_postal_codes',
    'address_area_assignments' => 'address_area_assignments',
    'resolution_gaps' => 'address_resolution_gaps',
],
```

- `states` and `cities` back the first-class `State` and `City` models
- `cities.state_id` is nullable; countries without a state/province level can still use country-scoped cities
- Addresses may store free-text `state` / `city` strings and optionally link via `state_id` / `city_id`

## Owner scoping

Instance address data is owner-scoped by default. The `owner_type` / `owner_id`
columns ship in the `addresses`, `addressables`, and `address_snapshots`
create-table migrations, and the models enforce scoping through `HasOwner` plus
a global `OwnerScope`. There is no backfill path: rows created without an owner
tuple stay ownerless and are invisible to tenant queries.

Intentional global rows remain possible only when the application explicitly
uses `OwnerContext::withOwner(null, ...)`. They are not included in tenant
queries unless the caller explicitly uses global context or opts into
`include_global`.

```php
'features' => [
    'owner' => [
        'enabled' => env('ADDRESSING_OWNER_ENABLED', true),
        'include_global' => env('ADDRESSING_OWNER_INCLUDE_GLOBAL', false),
        'auto_assign_on_create' => env('ADDRESSING_OWNER_AUTO_ASSIGN', true),
    ],
],
```

Use `OwnerContext::withOwner($owner, ...)` for tenant work and
`OwnerContext::withOwner(null, ...)` for deliberate global work. Reference
geography tables remain global and are not owner-scoped.

## Field grouping

Co-level subdivision + locality roles share one grouped control by default,
wherever both roles resolve and gate on the same parent level. Apps opting
out get one control per role.

```php
'fields' => [
    'group_subdivision_locality' => env('ADDRESSING_GROUP_SUBDIVISION_LOCALITY', true),
],
```

Consumers ask `shouldGroupSubdivisionLocality($countryId, $stateId)`; the
package returns false when the config is off, when either role is missing,
or when the two gates differ for the selected state.

## Models and Geography Providers

```php
'models' => [
    'country' => AIArmada\Addressing\Features\Geography\Models\AddressCountry::class,
    'state' => AIArmada\Addressing\Features\Geography\Models\State::class,
    'city' => AIArmada\Addressing\Features\Geography\Models\City::class,
    'area' => AIArmada\Addressing\Features\Geography\Models\AddressArea::class,
    'address' => null, // AddressLifecycle feature default; override with a subclass.
    'snapshot' => null, // Defaults to the AddressLifecycle feature model.
    'addressable' => null, // AddressLifecycle feature default; override with a subclass.
    'postal_code' => null, // PostalCodes feature default; override with a configured model.
    'resolution_gap' => null, // Defaults to the ResolutionGaps feature model.
    'area_assignment' => null, // AddressLifecycle feature default; override with a subclass.
    'area_name' => null, // Geography feature default; override with a subclass.
    'area_role' => null, // Geography feature default; override with a subclass.
    'area_state_link' => null, // Geography feature default; override with a subclass.
    'area_postal_code' => null, // PostalCodes feature default; override with a configured model.
    'area_relationship' => null, // Geography feature default; override with a subclass.
],
'geography' => [
    'providers' => [
        AIArmada\Addressing\Geography\Malaysia\MalaysiaGeographyProvider::class,
        AIArmada\Addressing\Geography\Singapore\SingaporeGeographyProvider::class,
        AIArmada\Addressing\Geography\Indonesia\IndonesiaGeographyProvider::class,
        AIArmada\Addressing\Geography\Brunei\BruneiGeographyProvider::class,
        // ... 224 more bundled providers (228 total);
        // see 05-country-data.md for the full set.
    ],
    'indonesia' => [
        'villages' => env('ADDRESSING_INDONESIA_VILLAGES', false),
    ],
],
```

Providers define country address levels such as state, district, municipality or locality. Resolve the profile by country with `CountryAddressProfileResolver`; do not assume that a provider level has the same meaning in every country.

`addressing.geography.indonesia.villages` (default `false`) opts into the
83,762 Indonesian village rows. The `village` assignment role is defined
regardless; the flag only controls whether village areas are seeded.

A `CountryGeographyProvider` also has a stable `providerKey()`. Keep that key unchanged when its imported `AddressAreaSource` key changes: it owns provider-seeded areas, aliases, roles, relationships, and State links across reseeds. The source key identifies a particular feed, while the provider key identifies its long-lived owner.

Keep source IDs, state codes, and city names stable; identity renames need an
explicit remap plan because seeders do not guess which old row to replace.

When a provider uses different first-level area roots for separate hierarchies, its State mappings may declare `hierarchy_types`. The same canonical `State` can then resolve the correct root for postal and administrative selectors independently.

Country-specific formatters are configured separately from geography providers:

```php
'formatters' => [
    AIArmada\Addressing\Geography\Malaysia\MalaysiaAddressFormatter::class,
    AIArmada\Addressing\Geography\Singapore\SingaporeAddressFormatter::class,
    AIArmada\Addressing\Geography\Indonesia\IndonesiaAddressFormatter::class,
    AIArmada\Addressing\Geography\Brunei\BruneiAddressFormatter::class,
    // ... 224 more bundled formatters (228 total, one per provider).
],
```

`AddressFormatter` resolves a formatter by `AddressData::countryCode` and falls back to the generic formatter when no country formatter is registered. This keeps formatting independent from geography seeding.

### Provider registration

Shipping a provider class is not enough to use it: a provider takes effect only when its class is listed in `addressing.geography.providers` (and its formatter in `addressing.formatters`). To enable a country, add both classes to the consuming app's published `config/addressing.php`:

```php
'geography' => [
    'providers' => [
        // ...
        AIArmada\Addressing\Geography\Vietnam\VietnamGeographyProvider::class,
    ],
],
'formatters' => [
    // ...
    AIArmada\Addressing\Geography\Vietnam\VietnamAddressFormatter::class,
],
```

Registration gates everything. Seeding iterates registered providers only, so `CountryGeographySeeder::execute('VN')` silently seeds nothing when Vietnam is absent. `CountryAddressProfileResolver` likewise resolves registered providers only: for an unregistered country it returns no profile, `hierarchies()` is empty, and `definitionForRole()` returns null for every role — which makes `AddressAssignmentWriter` reject any non-empty assignment map with "The selected address area role is not defined by the country address profile." Only the empty map (delete-all) succeeds without a profile. Boot validation rejects configured entries that do not exist or do not implement `CountryGeographyProvider`, so a typo fails fast instead of seeding silently.

## Navigation Links

Navigation link columns (`google_maps_url`, `waze_url`, `navigation_links`) are part of the `addresses` and `address_snapshots` table schemas. They use the configured JSON column type for `navigation_links`.

Manual URLs always win over generated URLs. See `12-navigation-links.md` for full priority rules.

## Place metadata

Address areas store the place itself. Alternate names, address roles, typed
relationships and postcode coverage are stored separately. Alternate names
have a type such as `common` or `abbreviation`; there is no unused language
column because locale-aware name selection is not implemented.

Typed relationships have their own `source`. A manual relationship and a provider relationship with the same parent, child, type, and hierarchy remain separate records, so reseeding a provider cannot remove a manual edge.

## Defaults

```php
'defaults' => [
    'country_code' => env('ADDRESS_DEFAULT_COUNTRY_CODE'),
],
```

When configured, `ADDRESS_DEFAULT_COUNTRY_CODE` is trimmed and normalized to
uppercase during package boot. Invalid or blank values are treated as unset.

The configured model classes must extend the corresponding core model. Core
relations and normalization use `ModelResolver`, so host subclasses are
applied consistently. Resolution gap persistence is owned by its feature; set
`models.resolution_gap` to a subclass of `AIArmada\Addressing\Features\ResolutionGaps\Models\ResolutionGap` only when an override is required.

## Seed

```php
'seed' => [
    // Example: keep non-production city seeds to Malaysia only.
    'full_city_countries' => ['MY'],
    // Seed bundled postcode datasets as part of the full addressing seed.
    'postal_codes' => true,
],
```

Outside production, the bundled `AddressingSeeder` only
seeds cities for these ISO2 codes, keeping local databases small. Production
always seeds the full city dataset. Empty (the shipped default) seeds
everything everywhere.

Set `seed.postal_codes` to `false` to skip the postcode tier in the full
seed. `PostalCodeSeeder` and `address:seed-postal-codes` always run when
invoked directly, regardless of this flag.

## Area Sources

```php
'area_sources' => [
    // App\Addressing\MalaysiaAddressAreaSource::class,
],
```

Register your `AddressAreaSource` implementations here. They become available to the `address:import-areas` command.

## Google Places

```php
'google_places' => [
    'enabled' => env('ADDRESSING_GOOGLE_PLACES_ENABLED'),
    'api_key' => env('ADDRESSING_GOOGLE_MAPS_API_KEY'),
    'server_api_key' => env('ADDRESSING_GOOGLE_PLACES_SERVER_API_KEY'),
    'link_resolution_enabled' => env('ADDRESSING_GOOGLE_PLACE_LINK_RESOLUTION_ENABLED'),
    'user_agent' => env('ADDRESSING_GOOGLE_PLACES_USER_AGENT'),
],
```

Link resolution follows only the documented Google Maps, shortener
(`maps.app.goo.gl`, `goo.gl`, `g.co`), and consent hosts. Every redirect,
including the final target, must stay on an approved host; unsupported
regional domains fail closed instead of being fetched. Server-side place
search and place-details calls are skipped when `server_api_key` is missing,
so the integration stays optional.

## OneMap Postcodes

```php
'onemap' => [
    'base_url' => env('ONEMAP_BASE_URL', 'https://www.onemap.gov.sg/api'),
    'email' => env('ONEMAP_EMAIL'),
    'password' => env('ONEMAP_PASSWORD'),
    'timeout' => 10,
    'connect_timeout' => 5,
    'retries' => 2,
],
```

Singapore postcodes are resolved on demand through SLA's OneMap API instead
of being bundled. Register for a OneMap account, then set `ONEMAP_EMAIL` and
`ONEMAP_PASSWORD`. The client caches the access token until shortly before
its reported expiry, refreshes it once on a 401, and retries 429/5xx
responses with backoff. OneMap usage requires attribution; see
`05-country-data.md`.

## Reference Pipelines

```php
'reference' => [
    'cldr_path' => env('ADDRESSING_CLDR_PATH'),
    'cldr_version' => env('ADDRESSING_CLDR_VERSION'),
],
```

`address:reference:cldr` diffs `states.json` against a local
[cldr-json](https://github.com/unicode-org/cldr-json) checkout. Point
`cldr_path` at the directory that directly contains `cldr-core/` (or pass
`--cldr` per run). Set `cldr_version` to the checked-out cldr-core version
after the first green run; later runs refuse checkouts that disagree, so
drift reports stay reproducible. See
[reference pipelines](19-reference-pipelines.md).

## Optional spatial columns

Each setting defaults to `false`. The options can be enabled independently on PostgreSQL.

| Config key | Environment variable | Database extension | Columns |
| --- | --- | --- | --- |
| `addressing.features.spatial.postgis` | `ADDRESSING_SPATIAL_POSTGIS` | `postgis` | `location` on addresses and areas, `boundary` on areas |
| `addressing.features.spatial.geohash` | `ADDRESSING_SPATIAL_GEOHASH` | `postgis` | `geohash` on addresses and areas, stored at 12 characters |
| `addressing.features.spatial.h3` | `ADDRESSING_SPATIAL_H3` | `h3` from h3-pg 4.2.3 or later | `h3_cell` on addresses and areas, stored at resolution 9 |

Have a database administrator provision the required extensions on the application database. Their types and functions must be visible on the connection's PostgreSQL search path. Enable the desired settings, refresh cached config if used, and run `php artisan migrate`. Disabled migrations stay pending so an existing installation can enable an option later. Keep these options disabled on SQLite.

PostgreSQL generates and indexes the point and cell columns from existing and future coordinates. Missing and out-of-range coordinates produce null spatial values. Boundaries start empty and are populated by the application. On existing installations, adding stored generated columns rewrites the affected tables, so schedule the migration for a suitable deployment window. Disabling a setting after migration disables its package query methods and leaves the stored columns and indexes in place.

See [Spatial queries](04-usage.md#spatial-queries) for the model scopes and boundary action.
