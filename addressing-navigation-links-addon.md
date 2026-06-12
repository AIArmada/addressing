---
title: Add Geo Provider Fields and Manual Google Maps/Waze Navigation Links to Addressing
status: implementation-guide
package: aiarmada/addressing
scope: additive change on top of an already implemented addressing package
---

# Add Geo Provider Fields and Manual Google Maps/Waze Navigation Links to Addressing

## Purpose

This document instructs an AI agent or developer to add **location intelligence fields** and **manual navigation links** to an existing `aiarmada/addressing` package.

This is an **additive change** on top of the existing addressing package. Do not redesign the package, do not migrate consumer packages, and do not build routing/logistics features.

This document has two parts:

1. Add the original geo/provider address fields:
   - `latitude`
   - `longitude`
   - `formatted_address`
   - `provider`
   - `provider_place_id`
2. Add the manual navigation link fields:
   - `google_maps_url`
   - `waze_url`
   - `navigation_links`

The original idea is that structured location data remains the foundation. Google Maps/Waze URLs are an additional convenience layer for real-world operations where admins often paste an exact map link from WhatsApp, Google Maps, or Waze. Architecture cantik, tapi user tetap paste link. Kita jangan lawan fitrah manusia. 😂

## Critical Correction From Previous Draft

A previous draft mentioned `latitude`, `longitude`, `formatted_address`, `provider`, and `provider_place_id` conceptually, but its sample migration only added:

```php
$table->text('google_maps_url')->nullable();
$table->text('waze_url')->nullable();
$table->{$jsonColumnType}('navigation_links')->nullable();
```

That is incomplete.

This corrected instruction **must add all eight fields** to the database migration, model/data objects, snapshot behavior, tests, and docs:

```txt
latitude
longitude
formatted_address
provider
provider_place_id
google_maps_url
waze_url
navigation_links
```

If any implementation agent only adds Google/Waze fields and skips the geo/provider fields, the implementation is incomplete.

## Non-Goals

Do not implement any of these in this change:

- No Google Places API integration.
- No Waze API integration.
- No URL expansion or unshortening.
- No server-side fetching of map URLs.
- No route optimization.
- No distance matrix.
- No ETA calculation.
- No traffic/toll calculation.
- No map preview scraping.
- No required subscription or API key.
- No changes to country or area import contracts.
- No physical migration of consumer package address columns.
- No Filament package implementation unless explicitly requested in a separate task.

This change only adds storage, DTO/action support, snapshots, and helper-generated fallback navigation links.

## Existing Package Assumptions

Assume the existing `aiarmada/addressing` package already has these or equivalent concepts:

- `Address` model.
- `AddressSnapshot` model or snapshot data structure.
- `AddressData` DTO/data object.
- `addresses` table.
- `address_snapshots` table or snapshot JSON payload.
- Config-driven table names using `config('addressing.tables.*')`.
- Config-driven JSON column type using `config('addressing.database.json_column_type')`.
- Migrations using UUID primary keys.
- No database-level foreign-key constraints or cascades.
- No `SoftDeletes`.
- Package docs under `packages/addressing/docs/`.

If the actual implementation differs, adapt names to the existing package conventions, but preserve the behavior in this document.

## Core Decision

The `addresses` table must support structured geo/provider data and optional manual navigation URLs.

### Original Geo/Provider Fields

Add these fields because they are part of the address/location foundation:

```php
$table->decimal('latitude', 10, 7)->nullable();
$table->decimal('longitude', 10, 7)->nullable();
$table->text('formatted_address')->nullable();
$table->string('provider')->nullable();
$table->string('provider_place_id')->nullable();
```

### Manual Navigation Link Fields

Add these fields because users/admins may paste exact map links manually:

```php
$table->text('google_maps_url')->nullable();
$table->text('waze_url')->nullable();
$table->{$jsonColumnType}('navigation_links')->nullable();
```

Why both direct columns and JSON?

- `google_maps_url` and `waze_url` are common enough to be convenient first-class columns.
- `navigation_links` allows future link types such as Apple Maps, OpenStreetMap, HERE Maps, Grab, or custom directions.
- Direct columns are easier to query, display, validate, and use in forms.
- JSON avoids another migration every time a new navigation provider is added.

## Database Migration Requirements

Create one new additive migration in:

```txt
packages/addressing/database/migrations/
```

Suggested name:

```txt
2026_XX_XX_XXXXXX_add_geo_provider_and_navigation_links_to_addresses.php
```

The migration must be safe and idempotent.

### Required Migration Fields

The migration must add these nullable fields to the configured `addresses` table:

```txt
latitude
longitude
formatted_address
provider
provider_place_id
google_maps_url
waze_url
navigation_links
```

The migration must also add the same fields to the configured `address_snapshots` table **if that table exists and snapshots are column-based**.

If snapshots are JSON-only, do not force new snapshot columns. Instead, update the snapshot creation logic so the snapshot JSON includes the same fields.

### Required Migration Rules

Follow the monorepo rules:

- Use configured table names.
- Use configured JSON column type.
- Do not add database foreign-key constraints.
- Do not add database cascades.
- Do not require a `down()` method.
- Do not use `SoftDeletes`.
- Do not touch unrelated tables.
- Keep the migration idempotent.

## Sample Migration

Use this as the implementation target. Adjust namespace/imports only if the package has a different established migration style.

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $jsonColumnType = config('addressing.database.json_column_type', 'json');

        $addressesTable = config('addressing.tables.addresses', 'addresses');
        $snapshotsTable = config('addressing.tables.snapshots', 'address_snapshots');

        if (Schema::hasTable($addressesTable)) {
            Schema::table($addressesTable, function (Blueprint $table) use ($addressesTable, $jsonColumnType): void {
                if (! Schema::hasColumn($addressesTable, 'latitude')) {
                    $table->decimal('latitude', 10, 7)->nullable();
                }

                if (! Schema::hasColumn($addressesTable, 'longitude')) {
                    $table->decimal('longitude', 10, 7)->nullable();
                }

                if (! Schema::hasColumn($addressesTable, 'formatted_address')) {
                    $table->text('formatted_address')->nullable();
                }

                if (! Schema::hasColumn($addressesTable, 'provider')) {
                    $table->string('provider')->nullable();
                }

                if (! Schema::hasColumn($addressesTable, 'provider_place_id')) {
                    $table->string('provider_place_id')->nullable();
                }

                if (! Schema::hasColumn($addressesTable, 'google_maps_url')) {
                    $table->text('google_maps_url')->nullable();
                }

                if (! Schema::hasColumn($addressesTable, 'waze_url')) {
                    $table->text('waze_url')->nullable();
                }

                if (! Schema::hasColumn($addressesTable, 'navigation_links')) {
                    $table->{$jsonColumnType}('navigation_links')->nullable();
                }
            });
        }

        if (Schema::hasTable($snapshotsTable)) {
            Schema::table($snapshotsTable, function (Blueprint $table) use ($snapshotsTable, $jsonColumnType): void {
                if (! Schema::hasColumn($snapshotsTable, 'latitude')) {
                    $table->decimal('latitude', 10, 7)->nullable();
                }

                if (! Schema::hasColumn($snapshotsTable, 'longitude')) {
                    $table->decimal('longitude', 10, 7)->nullable();
                }

                if (! Schema::hasColumn($snapshotsTable, 'formatted_address')) {
                    $table->text('formatted_address')->nullable();
                }

                if (! Schema::hasColumn($snapshotsTable, 'provider')) {
                    $table->string('provider')->nullable();
                }

                if (! Schema::hasColumn($snapshotsTable, 'provider_place_id')) {
                    $table->string('provider_place_id')->nullable();
                }

                if (! Schema::hasColumn($snapshotsTable, 'google_maps_url')) {
                    $table->text('google_maps_url')->nullable();
                }

                if (! Schema::hasColumn($snapshotsTable, 'waze_url')) {
                    $table->text('waze_url')->nullable();
                }

                if (! Schema::hasColumn($snapshotsTable, 'navigation_links')) {
                    $table->{$jsonColumnType}('navigation_links')->nullable();
                }
            });
        }
    }
};
```

### Migration Notes

Do not use `after(...)` unless the existing project convention requires it. Keeping the additive migration order simple avoids failures when columns do not exist yet.

Do not add indexes unless there is already an explicit package need. If the package already supports nearby search, then a later migration may add appropriate geo indexes. Do not add speculative indexes in this task.

## Model Updates

Update `Address` casts/fillable/guarded behavior according to the existing package style.

Required fields to support:

```php
'latitude'
'longitude'
'formatted_address'
'provider'
'provider_place_id'
'google_maps_url'
'waze_url'
'navigation_links'
```

If the model uses casts:

```php
protected function casts(): array
{
    return [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'navigation_links' => 'array',
        // keep existing casts here
    ];
}
```

If the package prefers floats for DTOs, convert decimals at the DTO boundary. Do not break existing model conventions.

Do the same for `AddressSnapshot` if it is an Eloquent model with column-based snapshots.

## AddressData Updates

Add optional properties to `AddressData`.

Use the package's existing data object style. If it uses Spatie Laravel Data, follow that style. If it uses a readonly PHP DTO, follow that style.

Required logical properties:

```php
public readonly ?float $latitude = null;
public readonly ?float $longitude = null;
public readonly ?string $formattedAddress = null;
public readonly ?string $provider = null;
public readonly ?string $providerPlaceId = null;
public readonly ?string $googleMapsUrl = null;
public readonly ?string $wazeUrl = null;

/** @var array<string, mixed> */
public readonly array $navigationLinks = [];
```

### Required AddressData Aliases

The DTO must accept these input aliases.

#### Geo/Provider Aliases

```txt
latitude               -> latitude
lat                    -> latitude
longitude              -> longitude
lng                    -> longitude
lon                    -> longitude
formatted_address      -> formattedAddress
formattedAddress       -> formattedAddress
provider               -> provider
provider_place_id      -> providerPlaceId
providerPlaceId        -> providerPlaceId
place_id               -> providerPlaceId
placeId                -> providerPlaceId
google_place_id        -> providerPlaceId
googlePlaceId          -> providerPlaceId
```

#### Manual Navigation Link Aliases

```txt
google_maps_url        -> googleMapsUrl
googleMapsUrl          -> googleMapsUrl
google_map_url         -> googleMapsUrl
googleMapUrl           -> googleMapsUrl
maps_url               -> googleMapsUrl
mapsUrl                -> googleMapsUrl
waze_url               -> wazeUrl
wazeUrl                -> wazeUrl
navigation_links       -> navigationLinks
navigationLinks        -> navigationLinks
external_links         -> navigationLinks
externalLinks          -> navigationLinks
```

Do not remove existing aliases for address fields such as `line1`, `address_line_1`, `street_address`, `postcode`, `postal_code`, or `zip_code`.

## Address Creation and Update Actions

If the package has `CreateAddressAction` and `UpdateAddressAction`, update them to support all eight fields:

```php
latitude
longitude
formatted_address
provider
provider_place_id
google_maps_url
waze_url
navigation_links
```

Before saving manual URLs, normalize URL values:

```php
$googleMapsUrl = app(NormalizeNavigationUrl::class)->normalize($data->googleMapsUrl);
$wazeUrl = app(NormalizeNavigationUrl::class)->normalize($data->wazeUrl);
```

Do not silently overwrite a manual URL with a generated URL.

Generated URLs should be output-only unless a future task explicitly requests caching generated URLs.

## Snapshot Behavior

Geo/provider data and manual navigation URLs must be copied into address snapshots.

When creating a snapshot from an `Address` or `AddressData`, copy:

```php
'latitude' => $data->latitude,
'longitude' => $data->longitude,
'formatted_address' => $data->formattedAddress,
'provider' => $data->provider,
'provider_place_id' => $data->providerPlaceId,
'google_maps_url' => $data->googleMapsUrl,
'waze_url' => $data->wazeUrl,
'navigation_links' => $data->navigationLinks,
```

Why?

If an event is published with a specific address, coordinates, provider place ID, and Google Maps/Waze link, that historical event should keep the same location snapshot even if the venue address is edited later.

If the existing snapshot system stores only JSON components, make sure the JSON includes the fields:

```json
{
  "latitude": 3.1712,
  "longitude": 101.6678,
  "formatted_address": "Masjid Wilayah Persekutuan, Jalan Tuanku Abdul Halim, 50480 Kuala Lumpur",
  "provider": "google",
  "provider_place_id": "example-place-id",
  "google_maps_url": "https://maps.app.goo.gl/example",
  "waze_url": "https://waze.com/ul?ll=3.1712,101.6678&navigate=yes",
  "navigation_links": {}
}
```

## URL Normalization

Create a small normalizer. Suggested class:

```txt
packages/addressing/src/Support/NormalizeNavigationUrl.php
```

or use an Action if that is the package style:

```txt
packages/addressing/src/Actions/NormalizeNavigationUrlAction.php
```

### Normalization Rules

The normalizer must:

- Accept `null` and return `null`.
- Trim whitespace.
- Convert empty string to `null`.
- Reject non-HTTP(S) schemes.
- Never fetch or expand the URL.
- Never follow redirects.
- Never call external APIs.
- Preserve the original URL if valid.

### Suggested Implementation

```php
<?php

declare(strict_types=1);

namespace AiArmada\Addressing\Support;

final class NormalizeNavigationUrl
{
    public function normalize(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $url = trim($url);

        if ($url === '') {
            return null;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if (! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        return $url;
    }
}
```

Do not hard-block hosts by default. Google and Waze share links may use several domains and short-link formats. If host allowlisting is needed, make it configurable later.

## Navigation Link Factory

Add a class that returns the best navigation links for an address.

Suggested class:

```txt
packages/addressing/src/Actions/BuildAddressNavigationLinksAction.php
```

Alternative name:

```txt
AddressNavigationUrlFactory
```

Use the naming convention already used by the package.

## Required Behavior

The action/factory must support:

```php
$links = app(BuildAddressNavigationLinksAction::class)->execute($address);
```

It should return a simple data object or array.

Suggested array shape:

```php
/**
 * @return array{
 *     google_maps_url: string|null,
 *     google_maps_source: string|null,
 *     waze_url: string|null,
 *     waze_source: string|null,
 *     links: array<string, mixed>
 * }
 */
```

## Priority Rules

### Google Maps Priority

Use this exact priority:

```txt
1. Manually saved google_maps_url
2. navigation_links.google_maps.url
3. Generated URL from Google provider_place_id plus query
4. Generated URL from latitude/longitude
5. Generated URL from formatted_address
6. null
```

Manual link must always win.

### Waze Priority

Use this exact priority:

```txt
1. Manually saved waze_url
2. navigation_links.waze.url
3. Generated URL from latitude/longitude
4. Generated URL from formatted_address
5. null
```

Manual link must always win.

## Generated Google Maps URL Rules

Generated Google Maps URLs must use official Maps URL style.

### Search by Coordinates

```txt
https://www.google.com/maps/search/?api=1&query={lat}%2C{lng}
```

### Search by Address

```txt
https://www.google.com/maps/search/?api=1&query={encoded_formatted_address}
```

### Search by Place ID

Google Maps search URLs require a `query` parameter. If using a Place ID, include both `query` and `query_place_id`.

```txt
https://www.google.com/maps/search/?api=1&query={encoded_query}&query_place_id={encoded_place_id}
```

Query should be one of:

```txt
latitude,longitude
formatted_address
name + formatted_address
```

Do not generate a `query_place_id` URL without `query`.

### Directions URL

Do not make directions URL the main stored URL unless the package already has a need for directions.

If implemented as extra output, use:

```txt
https://www.google.com/maps/dir/?api=1&destination={encoded_destination}&travelmode=driving
```

## Generated Waze URL Rules

### Navigate by Coordinates

```txt
https://waze.com/ul?ll={lat}%2C{lng}&navigate=yes
```

### Search by Address

```txt
https://waze.com/ul?q={encoded_formatted_address}
```

If coordinates exist, prefer coordinates for Waze.

## Encoding Rules

Use PHP URL helpers. Do not concatenate raw user input without encoding.

Recommended:

```php
$query = http_build_query([
    'api' => '1',
    'query' => $destination,
]);

$url = 'https://www.google.com/maps/search/?'.$query;
```

For Waze:

```php
$query = http_build_query([
    'll' => $latitude.','.$longitude,
    'navigate' => 'yes',
]);

$url = 'https://waze.com/ul?'.$query;
```

`http_build_query()` may encode the comma as `%2C`, which is acceptable.

## Example Build Action

```php
<?php

declare(strict_types=1);

namespace AiArmada\Addressing\Actions;

use AiArmada\Addressing\Data\AddressData;
use AiArmada\Addressing\Models\Address;

final class BuildAddressNavigationLinksAction
{
    /**
     * @return array{
     *     google_maps_url: string|null,
     *     google_maps_source: string|null,
     *     waze_url: string|null,
     *     waze_source: string|null
     * }
     */
    public function execute(Address|AddressData $address): array
    {
        $data = AddressData::from($address);

        return [
            'google_maps_url' => $this->googleMapsUrl($data),
            'google_maps_source' => $this->googleMapsSource($data),
            'waze_url' => $this->wazeUrl($data),
            'waze_source' => $this->wazeSource($data),
        ];
    }

    private function googleMapsUrl(AddressData $data): ?string
    {
        if ($data->googleMapsUrl !== null) {
            return $data->googleMapsUrl;
        }

        $manual = data_get($data->navigationLinks, 'google_maps.url');

        if (is_string($manual) && $manual !== '') {
            return $manual;
        }

        if ($data->provider === 'google' && $data->providerPlaceId !== null) {
            $query = $this->coordinateQuery($data) ?? $data->formattedAddress ?? $data->line1;

            if ($query !== null) {
                return 'https://www.google.com/maps/search/?'.http_build_query([
                    'api' => '1',
                    'query' => $query,
                    'query_place_id' => $data->providerPlaceId,
                ]);
            }
        }

        if (($coordinateQuery = $this->coordinateQuery($data)) !== null) {
            return 'https://www.google.com/maps/search/?'.http_build_query([
                'api' => '1',
                'query' => $coordinateQuery,
            ]);
        }

        if ($data->formattedAddress !== null) {
            return 'https://www.google.com/maps/search/?'.http_build_query([
                'api' => '1',
                'query' => $data->formattedAddress,
            ]);
        }

        return null;
    }

    private function wazeUrl(AddressData $data): ?string
    {
        if ($data->wazeUrl !== null) {
            return $data->wazeUrl;
        }

        $manual = data_get($data->navigationLinks, 'waze.url');

        if (is_string($manual) && $manual !== '') {
            return $manual;
        }

        if (($coordinateQuery = $this->coordinateQuery($data)) !== null) {
            return 'https://waze.com/ul?'.http_build_query([
                'll' => $coordinateQuery,
                'navigate' => 'yes',
            ]);
        }

        if ($data->formattedAddress !== null) {
            return 'https://waze.com/ul?'.http_build_query([
                'q' => $data->formattedAddress,
            ]);
        }

        return null;
    }

    private function coordinateQuery(AddressData $data): ?string
    {
        if ($data->latitude === null || $data->longitude === null) {
            return null;
        }

        return $data->latitude.','.$data->longitude;
    }

    private function googleMapsSource(AddressData $data): ?string
    {
        if ($data->googleMapsUrl !== null) {
            return 'manual';
        }

        if (data_get($data->navigationLinks, 'google_maps.url') !== null) {
            return 'navigation_links';
        }

        if ($data->provider === 'google' && $data->providerPlaceId !== null) {
            return 'generated_place_id';
        }

        if ($data->latitude !== null && $data->longitude !== null) {
            return 'generated_coordinates';
        }

        if ($data->formattedAddress !== null) {
            return 'generated_formatted_address';
        }

        return null;
    }

    private function wazeSource(AddressData $data): ?string
    {
        if ($data->wazeUrl !== null) {
            return 'manual';
        }

        if (data_get($data->navigationLinks, 'waze.url') !== null) {
            return 'navigation_links';
        }

        if ($data->latitude !== null && $data->longitude !== null) {
            return 'generated_coordinates';
        }

        if ($data->formattedAddress !== null) {
            return 'generated_formatted_address';
        }

        return null;
    }
}
```

Adjust property names to match the actual `AddressData` implementation.

## Validation Rules

Add reusable validation rules or documentation examples.

Recommended validation for manual inputs:

```php
'latitude' => ['nullable', 'numeric', 'between:-90,90'],
'longitude' => ['nullable', 'numeric', 'between:-180,180'],
'formatted_address' => ['nullable', 'string'],
'provider' => ['nullable', 'string', 'max:100'],
'provider_place_id' => ['nullable', 'string', 'max:255'],
'google_maps_url' => ['nullable', 'url', 'max:2048'],
'waze_url' => ['nullable', 'url', 'max:2048'],
'navigation_links' => ['nullable', 'array'],
```

Even though DB columns for URLs are `text`, keep form/request validation at `max:2048` unless the product explicitly needs longer share URLs.

Do not require host-specific validation by default.

If the application wants stricter validation, allow it to add its own rules at the consuming package layer.

## Filament Addressing Follow-Up

Do not implement this in the core change unless asked.

If `aiarmada/filament-addressing` already exists and the task explicitly includes UI, add fields to the address form:

```txt
Latitude
Longitude
Formatted Address
Provider
Provider Place ID
Google Maps URL
Waze URL
```

Recommended Filament behavior:

- Fields are nullable.
- Latitude/longitude are numeric inputs.
- Google Maps/Waze are nullable URL inputs.
- Show helper text: “Manual link wins over generated link.”
- Add suffix/open action if a URL is present.
- Do not require the fields.
- Do not validate by host unless configured.
- Show generated fallback links as read-only/infolist if no manual link exists.

Keep Filament as an adapter. Do not put domain logic in the Filament package.

## Consumer Package Behavior

After this change, packages using addressing should behave like this:

### MajlisIlmu / Events

- Institution/Masjid may store coordinates, provider data, `google_maps_url`, and `waze_url` on its primary address.
- Venue may store coordinates, provider data, `google_maps_url`, and `waze_url` on its primary address.
- Event location snapshot must copy these fields.
- Event public page should display manual links if present.
- If manual links are missing, display generated links when possible.

### Customers

- Customer addresses may store coordinates and manual links, but most commerce flows probably do not need map links.
- Do not show map links in checkout unless the product needs it.

### Shipping

- Origin/destination `AddressData` may include coordinates and navigation URLs.
- Provider payload mappers should not send Google/Waze URLs unless provider explicitly supports them.

### Orders

- Order address snapshots may preserve coordinates/provider data/navigation URLs if copied from customer or venue address.
- Do not update old order links when the source customer address changes.

### Tax / Cashier / Payment Gateways

- Ignore navigation URLs.
- Do not send them to payment gateways.
- Do not use them for tax calculation.

## Documentation Updates

Update these docs in the addressing package:

```txt
packages/addressing/docs/03-configuration.md
packages/addressing/docs/04-usage.md
packages/addressing/docs/99-troubleshooting.md
```

Add a new docs section if the package already has numbered follow-up docs:

```txt
packages/addressing/docs/12-navigation-links.md
```

If the user requested only one implementation MD file, do not create the docs file yet. Instead, include this docs content in the implementation checklist and let the implementation agent update actual package docs.

### Docs Must Explain

- `latitude`, `longitude`, `formatted_address`, `provider`, and `provider_place_id` are structured location fields.
- Manual links are optional.
- Manual links win over generated links.
- Coordinates/place IDs/formatted address are still preferred structured data.
- Google Maps URL opening does not require an API key.
- Programmatic Place ID lookup is separate and not required.
- Waze URL generation uses Waze deep links.
- No server-side URL fetching is performed.
- Snapshots preserve all geo/provider/navigation fields.

## Tests

Add tests in the `addressing` package only.

Use Pest and run with `--parallel`.

### Required Test Cases

#### Migration

- Adds `latitude`, `longitude`, `formatted_address`, `provider`, `provider_place_id`, `google_maps_url`, `waze_url`, and `navigation_links` to `addresses`.
- Adds the same fields to `address_snapshots` if the table exists and snapshots are column-based.
- Uses configured JSON column type for `navigation_links`.
- Does not add DB constraints or cascades.

#### AddressData

- Accepts `latitude`.
- Accepts `lat`.
- Accepts `longitude`.
- Accepts `lng`.
- Accepts `formatted_address`.
- Accepts `formattedAddress`.
- Accepts `provider`.
- Accepts `provider_place_id`.
- Accepts `place_id`.
- Accepts `google_maps_url`.
- Accepts `googleMapsUrl`.
- Accepts `maps_url`.
- Accepts `waze_url`.
- Accepts `wazeUrl`.
- Accepts `navigation_links`.
- Keeps existing address field aliases working.

#### Normalizer

- Returns `null` for `null`.
- Returns `null` for empty string.
- Trims whitespace.
- Accepts `https://maps.app.goo.gl/example`.
- Accepts `https://www.google.com/maps/place/example`.
- Accepts `https://waze.com/ul?ll=3.0738,101.5183&navigate=yes`.
- Rejects `javascript:alert(1)`.
- Rejects `ftp://example.com/file`.
- Does not perform HTTP requests.

#### BuildAddressNavigationLinksAction

- Manual Google Maps URL wins over generated URL.
- Manual Waze URL wins over generated URL.
- `navigation_links.google_maps.url` is used when direct `google_maps_url` is absent.
- `navigation_links.waze.url` is used when direct `waze_url` is absent.
- Google URL generated from Place ID includes both `query` and `query_place_id`.
- Google URL generated from coordinates uses Maps search URL with `api=1`.
- Google URL generated from formatted address uses encoded query.
- Waze URL generated from coordinates uses `ll` and `navigate=yes`.
- Waze URL generated from formatted address uses `q`.
- Returns `null` when no usable data exists.

#### Snapshot

- Snapshot created from an Address copies `latitude`.
- Snapshot created from an Address copies `longitude`.
- Snapshot created from an Address copies `formatted_address`.
- Snapshot created from an Address copies `provider`.
- Snapshot created from an Address copies `provider_place_id`.
- Snapshot created from an Address copies `google_maps_url`.
- Snapshot created from an Address copies `waze_url`.
- Snapshot created from an Address copies `navigation_links`.
- Changing the source Address later does not mutate the existing snapshot.

## Verification Commands

Run only package-scoped commands.

```bash
./vendor/bin/pest --parallel packages/addressing/tests
```

```bash
./vendor/bin/phpstan analyse packages/addressing/src --level=6
```

Run Pint only on changed files or changed package:

```bash
./vendor/bin/pint packages/addressing/src packages/addressing/database packages/addressing/tests
```

Check forbidden migration patterns:

```bash
rg -n -- "constrained\(|cascadeOnDelete\(" packages/addressing/database packages/addressing/src
```

Check config keys are read:

```bash
rg -n -- "config\('addressing\." packages/addressing/src packages/addressing/config packages/addressing/database
```

## Self-Review Checklist Before Returning This File

Before giving this file to the user, the assistant or implementation agent must confirm the file contains all of these exact terms in the migration section and test section:

```txt
latitude
longitude
formatted_address
provider
provider_place_id
google_maps_url
waze_url
navigation_links
```

The migration sample must include all eight fields for `addresses`.

The snapshot section must include all eight fields for `address_snapshots` or JSON snapshot payloads.

The `AddressData` section must include all eight logical fields or aliases.

The tests section must include migration, DTO, navigation link, and snapshot test cases for all relevant fields.

## Agent Checklist

### Agent A — Database and Models

- [ ] Read `CONTEXT-MAP.md`.
- [ ] Read `packages/addressing/CONTEXT.md`.
- [ ] Read `packages/addressing/docs/01-overview.md`.
- [ ] Read `packages/addressing/docs/03-configuration.md`.
- [ ] Create migration to add `latitude`, `longitude`, `formatted_address`, `provider`, `provider_place_id`, `google_maps_url`, `waze_url`, and `navigation_links`.
- [ ] Use configured table names.
- [ ] Use configured JSON column type.
- [ ] Do not add DB constraints/cascades.
- [ ] Update `Address` casts/fillable according to package style.
- [ ] Update `AddressSnapshot` if applicable.
- [ ] Add/adjust model tests.

### Agent B — Data Objects and Actions

- [ ] Update `AddressData` with geo/provider fields and URL fields.
- [ ] Add aliases for latitude/longitude/formatted/provider/provider_place_id.
- [ ] Add aliases for Google/Waze/navigation link fields.
- [ ] Add URL normalizer.
- [ ] Add `BuildAddressNavigationLinksAction` or equivalent factory.
- [ ] Update create/update address actions.
- [ ] Update snapshot action to copy all geo/provider/navigation fields.
- [ ] Add unit tests for aliases, normalizer, generated links, and snapshot behavior.

### Agent C — Documentation

- [ ] Update `04-usage.md`.
- [ ] Update `03-configuration.md` if any config is added.
- [ ] Update `99-troubleshooting.md`.
- [ ] Add `12-navigation-links.md` if the docs structure supports follow-up files.
- [ ] Explain original geo/provider fields.
- [ ] Explain manual link precedence rules.
- [ ] Include examples for Google Maps and Waze manual links.
- [ ] Include examples for generated fallback links.

### Agent D — QC

- [ ] Confirm the migration sample includes all eight fields.
- [ ] Run Pest with `--parallel` for `packages/addressing/tests`.
- [ ] Run PHPStan level 6 for `packages/addressing/src`.
- [ ] Run Pint on changed addressing files only.
- [ ] Grep for forbidden DB constraints/cascades.
- [ ] Confirm no external API calls were introduced.
- [ ] Confirm no URL expansion/fetching was introduced.

## Acceptance Criteria

The implementation is complete when:

- `addresses` can store `latitude`, `longitude`, `formatted_address`, `provider`, `provider_place_id`, `google_maps_url`, `waze_url`, and `navigation_links`.
- `address_snapshots` can preserve those fields when snapshots are column-based, or snapshot JSON includes those fields when snapshots are JSON-based.
- `AddressData` accepts geo/provider/navigation fields and common aliases.
- Manual Google Maps URL wins over generated Google Maps URL.
- Manual Waze URL wins over generated Waze URL.
- Generated Google Maps URLs work from Place ID, coordinates, or formatted address.
- Generated Waze URLs work from coordinates or formatted address.
- No Google/Waze API key is required.
- No external HTTP calls are made.
- Existing addressing behavior still works.
- Package docs explain how to use the feature.
- Package tests pass with `--parallel`.
- No database foreign-key constraints or cascades are added.

## Example Usage

### Create Address With Geo Provider Data and Manual Links

```php
use AiArmada\Addressing\Actions\CreateAddressAction;
use AiArmada\Addressing\Data\AddressData;

$address = app(CreateAddressAction::class)->execute(
    addressable: $venue,
    data: AddressData::from([
        'line1' => 'Jalan Tuanku Abdul Halim',
        'city' => 'Kuala Lumpur',
        'state' => 'Wilayah Persekutuan Kuala Lumpur',
        'postcode' => '50480',
        'countryCode' => 'MY',
        'latitude' => 3.1712,
        'longitude' => 101.6678,
        'formatted_address' => 'Masjid Wilayah Persekutuan, Jalan Tuanku Abdul Halim, 50480 Kuala Lumpur',
        'provider' => 'google',
        'provider_place_id' => 'example-place-id',
        'google_maps_url' => 'https://maps.app.goo.gl/example',
        'waze_url' => 'https://waze.com/ul?ll=3.1712,101.6678&navigate=yes',
    ]),
    type: 'primary',
    isPrimary: true,
);
```

### Build Navigation Links

```php
use AiArmada\Addressing\Actions\BuildAddressNavigationLinksAction;

$links = app(BuildAddressNavigationLinksAction::class)->execute($address);

$googleMapsUrl = $links['google_maps_url'];
$wazeUrl = $links['waze_url'];
```

### Snapshot Event Location

```php
use AiArmada\Addressing\Actions\CreateAddressSnapshotAction;
use AiArmada\Addressing\Data\AddressData;

app(CreateAddressSnapshotAction::class)->execute(
    snapshotable: $event,
    data: AddressData::from($venue->primaryAddress()),
    reason: 'event_location',
);
```

The snapshot must preserve all geo/provider/navigation fields.

## Final Rule

Structured location data is the foundation:

```txt
latitude
longitude
formatted_address
provider
provider_place_id
```

Manual navigation links are the convenience layer:

```txt
google_maps_url
waze_url
navigation_links
```

Manual URL is truth when present.

Generated URL is convenience when manual URL is absent.

Do not replace coordinates/place IDs/formatted addresses with map URLs only. Store both when available.
