# aiarmada/addressing — Master Implementation Instruction

This is the canonical implementation brief for creating `aiarmada/addressing` in the monorepo.

The agent must not guess package shape, schema, command names, data policy, or docs structure. Follow this document and the repository `AGENTS.md` exactly.

## 0. Non-negotiable context

Before editing code, the implementing agent must read, in this order:

1. Repository `AGENTS.md`.
2. `CONTEXT-MAP.md` at repo root, if present.
3. `packages/addressing/CONTEXT.md` from this instruction pack after copying it into the package.
4. Existing sibling package structure in `packages/*` to match Composer, service provider, config, migration, test and docs conventions.
5. `commerce-support` context/docs before implementing owner scoping or shared primitives.

If instructions conflict, follow the strictest rule. Security, data isolation, correctness and package boundaries win over convenience.

## 1. Package objective

Create `aiarmada/addressing`, a reusable Laravel package for address handling across the monorepo.

The package must provide:

- Normalized address value objects.
- Reusable `Address` Eloquent model.
- `HasAddresses` polymorphic attachment trait.
- Historical address snapshots.
- Country reference data.
- Generic administrative/locality area schema.
- Strict contract for user-supplied state/city/district/locality data.
- Formatting, normalization and mapping utilities.
- Import commands/actions for country and area data.
- Full package docs.
- Tests.

The package must not provide:

- Venue management.
- Institution/masjid/surau management.
- Event location management.
- Shipping provider-specific persistence.
- Cashier/gateway-specific models.
- Filament UI.
- Bundled state/city/district/postcode/world locality data.

Filament must be a later optional adapter package such as `aiarmada/filament-addressing`.

## 2. Naming decisions

Use package name:

```txt
aiarmada/addressing
```

Use namespace:

```php
AIArmada\Addressing
```

Use config file:

```txt
config/addressing.php
```

Use short `address_` database table prefix:

```txt
address_countries
address_areas
addresses
addressables
address_snapshots
```

Do not use `addressing_` table prefix.

Use developer-facing address field names:

```txt
line1
line2
line3
city
district
state
postcode
country
country_code
```

Use global/admin-area reference columns for advanced use:

```txt
admin_area_1_id
admin_area_2_id
admin_area_3_id
admin_area_4_id
```

Reason: not every country has “state”. Malaysia has both states and federal territories. The package can keep `state` as a simple familiar text field while using `admin_area_*` references for globally correct hierarchy.

## 3. Data policy

### 3.1 Bundled default data

The package must bundle only ISO 3166-1 country/territory data.

Required file:

```txt
packages/addressing/resources/data/countries.php
```

The country/territory data must be imported into `address_countries` by:

```bash
php artisan address:seed-countries
```

or by calling:

```php
AIArmada\Addressing\Actions\SeedAddressCountriesAction
```

### 3.2 Not bundled

The package must not bundle default data for:

- States.
- Federal territories.
- Provinces.
- Prefectures.
- Emirates.
- Districts.
- Cities.
- Towns.
- Villages.
- Mukim.
- Neighbourhoods.
- Postcodes.

These must be inserted by users through the area-source contract.

### 3.3 nnjeim/world relationship

`nnjeim/world` is not a dependency of the core package.

It may be used as an optional comparison source or later adapter because it exposes countries, states, cities, currencies, timezones, languages and geolocation features, and its repo contains JSON resources such as `resources/json/countries.json`, `states.json` and `cities.json`.

The core package must include comparison/audit instructions, but must not require `nnjeim/world`.

Use Composer `suggest`, not `require`:

```json
{
  "suggest": {
    "nnjeim/world": "Optional source for comparing/importing country, state and city data through an adapter."
  }
}
```

A later adapter package may be created:

```txt
aiarmada/addressing-world
```

## 4. Database rules from AGENTS.md

All migrations must follow these rules:

- Use `uuid('id')->primary()` for primary keys.
- Use `foreignUuid('...')` for foreign-key columns only.
- Do not add database foreign-key constraints.
- Do not use `->constrained()`.
- Do not use `->cascadeOnDelete()`.
- Do not use `SoftDeletes`.
- Keep migrations safe and idempotent.
- No `down()` method required.
- Any JSON column must use configurable column type from `config('addressing.database.json_column_type')`.

Correct pattern:

```php
$jsonColumnType = config('addressing.database.json_column_type', 'json');

Schema::create(config('addressing.tables.countries'), function (Blueprint $table) use ($jsonColumnType): void {
    $table->uuid('id')->primary();
    $table->{$jsonColumnType}('metadata')->nullable();
});
```

Forbidden:

```php
$table->foreignUuid('country_id')->constrained()->cascadeOnDelete();
```

Required verification:

```bash
rg -n -- "constrained\(|cascadeOnDelete\(" packages/addressing/database packages/addressing/src
```

This must return no matches.

## 5. Config file

Create:

```txt
packages/addressing/config/addressing.php
```

Required shape:

```php
<?php

declare(strict_types=1);

return [
    'database' => [
        'json_column_type' => env('ADDRESS_JSON_COLUMN_TYPE', 'json'),
    ],

    'tables' => [
        'countries' => 'address_countries',
        'areas' => 'address_areas',
        'addresses' => 'addresses',
        'addressables' => 'addressables',
        'snapshots' => 'address_snapshots',
    ],

    'defaults' => [
        'country_code' => env('ADDRESS_DEFAULT_COUNTRY_CODE', 'MY'),
        'locale' => env('ADDRESS_DEFAULT_LOCALE', 'ms-MY'),
    ],

    'features' => [
        'owner_scoping' => false,
    ],

    'area_sources' => [
        // App\Addressing\MalaysiaAddressAreaSource::class,
    ],
];
```

Do not add unused config keys.

## 6. Migrations

Create these migrations only.

### 6.1 `address_countries`

This table stores ISO 3166-1 country/territory address entities. Do not describe all rows as sovereign countries.

Required columns:

```php
$table->uuid('id')->primary();
$table->string('iso2', 2)->unique();
$table->string('iso3', 3)->nullable()->index();
$table->string('numeric_code', 3)->nullable()->index();
$table->string('entity_type')->default('country')->index();
$table->boolean('is_independent')->nullable()->index();
$table->string('name');
$table->string('official_name')->nullable();
$table->string('common_name')->nullable();
$table->string('native_name')->nullable();
$table->string('emoji')->nullable();
$table->string('phone_code')->nullable();
$table->{$jsonColumnType}('calling_codes')->nullable();
$table->string('capital')->nullable();
$table->decimal('capital_latitude', 10, 7)->nullable();
$table->decimal('capital_longitude', 10, 7)->nullable();
$table->decimal('latitude', 10, 7)->nullable();
$table->decimal('longitude', 10, 7)->nullable();
$table->string('region')->nullable()->index();
$table->string('subregion')->nullable()->index();
$table->{$jsonColumnType}('currency_codes')->nullable();
$table->string('default_currency_code', 3)->nullable();
$table->{$jsonColumnType}('language_codes')->nullable();
$table->{$jsonColumnType}('timezones')->nullable();
$table->{$jsonColumnType}('top_level_domains')->nullable();
$table->{$jsonColumnType}('metadata')->nullable();
$table->timestamps();
```

`entity_type` examples:

```txt
country
territory
dependency
constituent_country
associated_state
special_area
disputed_or_observer
```

`is_independent` must be nullable. Do not use it to decide whether an address is valid. It is a filtering/display aid only.

### 6.2 `address_areas`

Required columns:

```php
$table->uuid('id')->primary();
$table->foreignUuid('country_id')->nullable()->index();
$table->foreignUuid('parent_id')->nullable()->index();
$table->string('country_code', 2)->index();
$table->string('type')->index();
$table->unsignedSmallInteger('level')->nullable()->index();
$table->string('name');
$table->string('native_name')->nullable();
$table->string('code')->nullable()->index();
$table->string('slug')->index();
$table->decimal('latitude', 10, 7)->nullable();
$table->decimal('longitude', 10, 7)->nullable();
$table->string('source')->index();
$table->string('source_id')->index();
$table->string('parent_source_id')->nullable()->index();
$table->{$jsonColumnType}('source_payload')->nullable();
$table->timestampTz('synced_at')->nullable();
$table->{$jsonColumnType}('metadata')->nullable();
$table->timestamps();

$table->unique(['source', 'source_id']);
$table->index(['country_code', 'type', 'name']);
```

`type` examples:

```txt
state
federal_territory
province
prefecture
emirate
county
district
city
town
mukim
subdistrict
village
neighbourhood
postcode_area
```

### 6.3 `addresses`

Required columns:

```php
$table->uuid('id')->primary();
$table->nullableMorphs('owner'); // only if owner scoping is enabled/configured; otherwise omit until commerce-support integration is implemented.
$table->foreignUuid('country_id')->nullable()->index();
$table->foreignUuid('admin_area_1_id')->nullable()->index();
$table->foreignUuid('admin_area_2_id')->nullable()->index();
$table->foreignUuid('admin_area_3_id')->nullable()->index();
$table->foreignUuid('admin_area_4_id')->nullable()->index();
$table->string('label')->nullable();
$table->string('line1')->nullable();
$table->string('line2')->nullable();
$table->string('line3')->nullable();
$table->string('building_name')->nullable();
$table->string('unit_number')->nullable();
$table->string('floor')->nullable();
$table->string('block')->nullable();
$table->string('street_number')->nullable();
$table->string('street_name')->nullable();
$table->string('neighbourhood')->nullable();
$table->string('village')->nullable();
$table->string('district')->nullable();
$table->string('city')->nullable()->index();
$table->string('state')->nullable()->index();
$table->string('postcode')->nullable()->index();
$table->string('country')->nullable();
$table->string('country_code', 2)->nullable()->index();
$table->text('raw_address')->nullable();
$table->text('formatted_address')->nullable();
$table->{$jsonColumnType}('formatted_lines')->nullable();
$table->{$jsonColumnType}('components')->nullable();
$table->decimal('latitude', 10, 7)->nullable();
$table->decimal('longitude', 10, 7)->nullable();
$table->string('geohash')->nullable()->index();
$table->string('geo_precision')->nullable();
$table->string('provider')->nullable()->index();
$table->string('provider_place_id')->nullable()->index();
$table->{$jsonColumnType}('provider_payload')->nullable();
$table->string('validation_status')->default('unverified')->index();
$table->timestampTz('validated_at')->nullable();
$table->{$jsonColumnType}('metadata')->nullable();
$table->timestamps();
```

No soft deletes.

### 6.4 `addressables`

Required columns:

```php
$table->uuid('id')->primary();
$table->foreignUuid('address_id')->index();
$table->morphs('addressable');
$table->string('type')->default('primary')->index();
$table->string('label')->nullable();
$table->boolean('is_primary')->default(false)->index();
$table->timestampTz('valid_from')->nullable();
$table->timestampTz('valid_until')->nullable();
$table->{$jsonColumnType}('metadata')->nullable();
$table->timestamps();
$table->index(['addressable_type', 'addressable_id', 'type']);
```

### 6.5 `address_snapshots`

Required columns:

```php
$table->uuid('id')->primary();
$table->foreignUuid('address_id')->nullable()->index();
$table->morphs('snapshotable');
$table->string('reason')->nullable()->index();
$table->string('label')->nullable();
$table->string('line1')->nullable();
$table->string('line2')->nullable();
$table->string('line3')->nullable();
$table->string('city')->nullable();
$table->string('district')->nullable();
$table->string('state')->nullable();
$table->string('postcode')->nullable();
$table->string('country')->nullable();
$table->string('country_code', 2)->nullable()->index();
$table->text('formatted_address')->nullable();
$table->{$jsonColumnType}('formatted_lines')->nullable();
$table->{$jsonColumnType}('components')->nullable();
$table->decimal('latitude', 10, 7)->nullable();
$table->decimal('longitude', 10, 7)->nullable();
$table->{$jsonColumnType}('metadata')->nullable();
$table->timestamps();
```

## 7. Models

Create models:

```txt
src/Models/AddressCountry.php
src/Models/AddressArea.php
src/Models/Address.php
src/Models/Addressable.php
src/Models/AddressSnapshot.php
```

Model rules:

- Use `Illuminate\Database\Eloquent\Concerns\HasUuids`.
- Do not set `protected $table`.
- Implement `getTable()` using config.
- Use explicit casts.
- Use immutable date casts for timestamps such as `validated_at`, `synced_at`, `valid_from`, `valid_until`.
- Type relations with PHPDoc generics.
- Do not use `SoftDeletes`.
- If application-level delete cleanup is needed, implement in `booted()` carefully without DB cascades.

Example `getTable()`:

```php
public function getTable(): string
{
    return config('addressing.tables.countries', 'address_countries');
}
```

## 8. Data objects

Use `spatie/laravel-data` if already available in the monorepo. If not already available, do not add dependency without approval; create final immutable PHP DTOs instead.

Required data objects:

```txt
src/Data/AddressData.php
src/Data/AddressAreaData.php
src/Data/AddressCountryData.php
src/Data/AddressSnapshotData.php
src/Data/ImportAddressAreasResultData.php
src/Data/ImportAddressAreaFailureData.php
```

### 8.1 `AddressData`

Required properties:

```php
public function __construct(
    public readonly ?string $line1 = null,
    public readonly ?string $line2 = null,
    public readonly ?string $line3 = null,
    public readonly ?string $city = null,
    public readonly ?string $district = null,
    public readonly ?string $state = null,
    public readonly ?string $postcode = null,
    public readonly ?string $country = null,
    public readonly ?string $countryCode = null,
    public readonly ?string $formatted = null,
    public readonly ?float $latitude = null,
    public readonly ?float $longitude = null,
    public readonly array $components = [],
    public readonly array $metadata = [],
) {}
```

Aliases accepted by `AddressData::from()`:

```txt
address_line_1 => line1
address_line_2 => line2
street_address => line1
shipping_street_address => line1
postal_code => postcode
zip_code => postcode
postCode => postcode
countryCode => countryCode
country_code => countryCode
```

The canonical internal country value is ISO2 `countryCode`, for example `MY`.

### 8.2 `AddressAreaData`

Required properties:

```php
public function __construct(
    public readonly string $source,
    public readonly string $sourceId,
    public readonly string $countryCode,
    public readonly string $type,
    public readonly string $name,
    public readonly ?string $nativeName = null,
    public readonly ?string $code = null,
    public readonly ?string $parentSourceId = null,
    public readonly ?int $level = null,
    public readonly ?float $latitude = null,
    public readonly ?float $longitude = null,
    public readonly array $metadata = [],
    public readonly array $sourcePayload = [],
) {}
```

## 9. Contracts

Create:

```txt
src/Contracts/AddressAreaSource.php
src/Contracts/AddressFormatter.php
src/Contracts/AddressNormalizer.php
```

### 9.1 `AddressAreaSource`

Exact shape:

```php
<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

use AIArmada\Addressing\Data\AddressAreaData;
use Illuminate\Support\LazyCollection;

interface AddressAreaSource
{
    public function key(): string;

    /**
     * @return LazyCollection<int, AddressAreaData>
     */
    public function areas(): LazyCollection;
}
```

## 10. Actions

Create actions:

```txt
src/Actions/SeedAddressCountriesAction.php
src/Actions/ImportAddressAreasAction.php
src/Actions/CreateAddressSnapshotAction.php
src/Actions/NormalizeAddressDataAction.php
src/Actions/FormatAddressAction.php
```

### 10.1 `SeedAddressCountriesAction`

Behavior:

- Load `resources/data/countries.php`.
- Validate every row has `iso2`, `iso3`, `name`.
- Upsert by `iso2`.
- Update changed metadata safely.
- Return count created/updated/skipped.
- Be idempotent.

### 10.2 `ImportAddressAreasAction`

Behavior:

- Accept `AddressAreaSource $source` and `bool $dryRun = false`.
- Read every `AddressAreaData` row lazily.
- Validate required fields: source, sourceId, countryCode, type, name.
- Resolve country by ISO2 `countryCode` from `address_countries`.
- Fail row when country is missing.
- Upsert by `source + source_id`.
- Resolve parent by `source + parent_source_id`.
- Fail row when parent is required but missing.
- Store source, source_id, parent_source_id, source_payload, synced_at.
- Generate slug from name.
- Return detailed result object with created, updated, skipped, failed counts and row failures.
- Never silently skip invalid data.

## 11. Commands

Create commands:

```txt
src/Commands/SeedAddressCountriesCommand.php
src/Commands/ImportAddressAreasCommand.php
src/Commands/ImportAddressAreasCsvCommand.php
```

Names:

```bash
php artisan address:seed-countries
php artisan address:import-areas {source} {--dry-run}
php artisan address:import-areas-csv {path} {--source=} {--dry-run}
```

`address:import-areas` resolves `{source}` against configured `addressing.area_sources` classes by calling each source `key()`.

`address:import-areas-csv` must map CSV headers:

```csv
source_id,country_code,type,name,native_name,code,parent_source_id,level,latitude,longitude
```

into `AddressAreaData` and call the same `ImportAddressAreasAction`.

Do not duplicate import logic in the command.

## 12. Traits

Create:

```txt
src/Traits/HasAddresses.php
```

Required methods:

```php
public function addresses(): MorphToMany;
public function primaryAddress(?string $type = null): ?Address;
public function addressesOfType(string $type): Collection;
public function attachAddress(Address $address, string $type = 'primary', bool $isPrimary = false, ?string $label = null): Addressable;
public function setPrimaryAddress(Address $address, string $type = 'primary'): Addressable;
```

`setPrimaryAddress()` must unset existing primary addressables of the same type for the same model before setting the new one.

## 13. Casts and support

Create:

```txt
src/Casts/AddressDataCast.php
src/Support/ArrayAddressAreaSource.php
src/Support/CsvAddressAreaSource.php
src/Support/AddressAliasMap.php
```

`AddressDataCast` must allow JSON columns such as shipment origin/destination addresses to be cast to/from `AddressData`.

## 14. Country data audit requirement

Before finalizing a PR, the agent must audit country data.

Minimum audit steps:

1. Verify `resources/data/countries.php` contains every ISO 3166-1 alpha-2 country/territory address entity expected by the project. The expected count is 249 ISO entries, not 249 sovereign countries.
2. Install or inspect `nnjeim/world` in a disposable branch/worktree if needed.
3. Compare against `nnjeim/world/resources/json/countries.json`.
4. Confirm ISO2, ISO3, numeric code, entity type, independence flag, name, phone code, currency and timezone differences.
5. Record intentional differences in `packages/addressing/docs/05-country-data.md`.
6. Do not copy `nnjeim/world` data blindly into this package without license/provenance review.

Suggested local comparison command after `nnjeim/world` is available:

```bash
php artisan address:audit-countries --against=vendor/nnjeim/world/resources/json/countries.json
```

If this command is not implemented yet, compare with a one-off local script but do not commit that script unless it becomes a tested package command.

## 15. Multi-agent work allocation

Agents must not overlap. Each agent owns only the listed files.

### Agent A — Context and skeleton

Owns:

```txt
packages/addressing/composer.json
packages/addressing/CONTEXT.md
packages/addressing/config/addressing.php
packages/addressing/src/AddressingServiceProvider.php
```

Tasks:

- Create package skeleton matching sibling package conventions.
- Register config publish tag `address-config`.
- Register migrations publish tag `address-migrations`.
- Register country data publish tag only if sibling packages publish resources; otherwise keep internal.
- Register commands.
- Add Composer suggest for `nnjeim/world`.

Does not edit migrations, models, docs, tests except package setup smoke tests.

### Agent B — Migrations and models

Owns:

```txt
packages/addressing/database/migrations/*
packages/addressing/src/Models/*
packages/addressing/database/factories/*
```

Tasks:

- Implement migrations exactly as section 6.
- Implement models exactly as section 7.
- No DB constraints/cascades.
- No soft deletes.
- Add application-level cleanup only when required.

Does not edit commands/actions/docs.

### Agent C — Data objects, casts and normalization

Owns:

```txt
packages/addressing/src/Data/*
packages/addressing/src/Casts/*
packages/addressing/src/Support/AddressAliasMap.php
packages/addressing/src/Actions/NormalizeAddressDataAction.php
packages/addressing/src/Actions/FormatAddressAction.php
```

Tasks:

- Implement canonical DTOs.
- Implement alias mapping.
- Implement formatter fallback rules.
- Implement JSON cast.

Does not edit migrations/models/commands.

### Agent D — Country seeding

Owns:

```txt
packages/addressing/resources/data/countries.php
packages/addressing/resources/data/countries.audit.json
packages/addressing/src/Actions/SeedAddressCountriesAction.php
packages/addressing/src/Commands/SeedAddressCountriesCommand.php
packages/addressing/database/seeders/AddressCountrySeeder.php
```

Tasks:

- Use bundled country data.
- Ensure idempotent upsert by ISO2.
- Implement `address:seed-countries`.
- Add country seed tests.
- Audit against `nnjeim/world` before final PR.

Does not edit area import.

### Agent E — Area source/import pipeline

Owns:

```txt
packages/addressing/src/Contracts/AddressAreaSource.php
packages/addressing/src/Actions/ImportAddressAreasAction.php
packages/addressing/src/Commands/ImportAddressAreasCommand.php
packages/addressing/src/Commands/ImportAddressAreasCsvCommand.php
packages/addressing/src/Support/ArrayAddressAreaSource.php
packages/addressing/src/Support/CsvAddressAreaSource.php
```

Tasks:

- Implement strict area import contract.
- Implement dry-run.
- Implement source/source_id idempotency.
- Implement parent resolution.
- Implement CSV import via same action.

Does not edit countries.

### Agent F — Address relationships and snapshots

Owns:

```txt
packages/addressing/src/Traits/HasAddresses.php
packages/addressing/src/Actions/CreateAddressSnapshotAction.php
```

Tasks:

- Implement addressables relationship helpers.
- Implement snapshot creation from `Address` or `AddressData`.
- Ensure old snapshots are immutable by convention and docs.

Does not edit import pipeline.

### Agent G — Tests

Owns:

```txt
packages/addressing/tests/*
```

Tasks:

- Write Pest tests for every public behavior.
- Use `--parallel` for every Pest run.
- Add tests for no DB constraints by migration inspection if practical.
- Add idempotency tests.
- Add invalid row tests.
- Add alias tests.

Does not edit implementation unless fixing test failures in coordination with owning agent.

### Agent H — Docs

Owns:

```txt
packages/addressing/docs/*
```

Tasks:

- Keep docs canonical.
- Include examples for users to seed/import their own data.
- Include country data audit notes.
- Include troubleshooting for missing countries, duplicate source IDs, missing parents, JSON column types and owner scoping.

Does not edit code.

## 16. Required tests

Minimum tests:

```txt
AddressDataTest
- maps line1/line2 aliases
- maps address_line_1/address_line_2
- maps street_address
- maps postal_code/zip_code/postCode to postcode
- maps countryCode/country_code to countryCode

SeedAddressCountriesActionTest
- seeds bundled countries
- is idempotent
- includes MY Malaysia with ISO2 MY and ISO3 MYS
- stores calling codes/currency/timezones as arrays

ImportAddressAreasActionTest
- imports valid hierarchy
- dry-run creates nothing
- fails missing country
- fails missing parent
- upserts source/source_id
- records source_payload and synced_at

HasAddressesTest
- attaches address to model
- sets primary address
- unsets old primary of same type
- preserves primary of different type

CreateAddressSnapshotActionTest
- snapshots address fields
- snapshot remains unchanged when original address changes

AddressDataCastTest
- casts JSON to AddressData
- serializes AddressData to JSON
```

Run per package:

```bash
./vendor/bin/pest --parallel packages/addressing/tests
./vendor/bin/phpstan analyse packages/addressing/src --level=6
./vendor/bin/pint packages/addressing/src packages/addressing/config packages/addressing/database packages/addressing/tests
rg -n -- "constrained\(|cascadeOnDelete\(" packages/addressing/database packages/addressing/src
```

Do not run repo-wide Pint.

## 17. Required docs

Create these docs with YAML frontmatter and `title:`:

```txt
packages/addressing/docs/01-overview.md
packages/addressing/docs/02-installation.md
packages/addressing/docs/03-configuration.md
packages/addressing/docs/04-usage.md
packages/addressing/docs/05-country-data.md
packages/addressing/docs/99-troubleshooting.md
```

Examples must be copy-paste ready and include namespaces/imports.

## 18. Success criteria

The work is done only when:

- Package skeleton exists and autoloads.
- All migrations follow AGENTS database rules.
- Models use UUIDs and configurable `getTable()`.
- Country seed data exists and seeds idempotently.
- Area data can be imported by custom source, array source and CSV source.
- No state/city/district data is bundled by default.
- AddressData normalizes existing monorepo naming variants.
- HasAddresses works on arbitrary models.
- Snapshots work for orders/events/shipments.
- Docs are complete.
- Tests pass with `--parallel`.
- PHPStan level 6 passes for package src.
- No forbidden DB constraints/cascades exist.

## 19. Explicit non-goals for v1

Do not implement these in v1:

- Filament UI.
- Geocoding providers.
- Postcode validation by country.
- Full UPU S42 formatting engine.
- `nnjeim/world` hard dependency.
- `aiarmada/addressing-world` adapter package.
- Migration of existing monorepo packages to use addressing.

Mention these as future work only.
