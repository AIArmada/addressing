---
title: Usage
---

# Usage

## AddressData — Canonical Value Object

```php
use AIArmada\Addressing\Data\AddressData;

$address = AddressData::from([
    'line1' => '123 Jalan Ampang',
    'city' => 'Kuala Lumpur',
    'state' => 'Wilayah Persekutuan',
    'postcode' => '50450',
    'country' => 'Malaysia',
    'countryCode' => 'MY',
]);
```

Aliases accepted by `AddressData::from()`:

| Input | Maps to |
|-------|---------|
| `address_line_1` | `line1` |
| `address_line_2` | `line2` |
| `street_address` | `line1` |
| `shipping_street_address` | `line1` |
| `postal_code` | `postcode` |
| `zip_code` | `postcode` |
| `country_code` | `countryCode` |
| `country_id` / `countryId` | `countryId` |
| `state_id` / `stateId` | `stateId` |
| `city_id` / `cityId` | `cityId` |

Geographic values use the canonical `latitude`, `longitude`, and
`providerPlaceId`/`provider_place_id` fields. The removed `lat`, `lng`,
`lon`, `google_place_id`, and `googlePlaceId` aliases are intentionally not
normalized.

## Seed Country Data

```php
use AIArmada\Addressing\Actions\SeedAddressCountriesAction;

app(SeedAddressCountriesAction::class)->execute();
// ['created' => 250, 'updated' => 0, 'skipped' => 0]
```

Or via CLI:

```bash
php artisan address:seed-countries
```

## States and Cities

First-class geography models sit beside free-text address fields:

```php
use AIArmada\Addressing\Models\Address;
use AIArmada\Addressing\Models\City;
use AIArmada\Addressing\Models\State;

$state = State::query()->where('code', 'SGR')->first();
$city = City::query()->where('country_id', $state->country_id)->where('name', 'Shah Alam')->first();

$address = Address::query()->create([
    'line1' => '123 Jalan Ampang',
    'city' => $city->name,
    'state' => $state->name,
    'state_id' => $state->id,
    'city_id' => $city->id,
    'postcode' => '40000',
    'country_code' => 'MY',
]);

$address->state; // State model
$address->city;  // City model
$state->cities;  // HasMany cities when this country uses a state relationship
```

`City::state()` may be null. Use the selected country's address profile to decide whether the UI should ask for a state, province, prefecture, county, municipality, or no parent region.

`NormalizeAddressDataAction` treats reference IDs as authoritative. When only
country/state/city names or codes are supplied, it resolves matching reference
rows and returns their IDs and canonical names. Explicit IDs are validated for
existence and country/state consistency; no database foreign-key constraint is
added.

When a reference ID is present, its persisted name and country relationship
win over conflicting free-text fields. If an explicit reference ID cannot be
resolved, normalization throws instead of preserving a stale ID.

### Seed Malaysia geography

```php
use AIArmada\Addressing\Actions\SeedCountryGeographiesAction;

// After address:seed-countries
app(SeedCountryGeographiesAction::class)->execute('MY');
```

This uses the bundled Malaysia country provider. Other country providers can define different address structures without changing `State`, `City`, or `AddressArea` core tables.

Malaysia exposes one first-level region type whose values are either a state or a federal territory. The provider then exposes two separate hierarchies: postal/address geography (`region → locality / precinct / kampung`) and administrative/land geography (`region → district / division / jajahan → mukim / subdistrict / bandar / pekan`). A federal territory is never wrapped in a duplicate postal-town node.

### Seed Singapore geography

```php
use AIArmada\Addressing\Actions\SeedCountryGeographiesAction;

// After address:seed-countries
app(SeedCountryGeographiesAction::class)->execute('SG');
```

Singapore exposes two separate hierarchies: postal/delivery geography (`postal district → postal sector`, 28 districts and 81 sectors) and administrative/planning geography (`planning region → planning area`, 5 URA regions and 55 planning areas). The five `State` rows are the ISO 3166-2 community development council districts; they link to matching district areas but are not roots of either hierarchy, because CDC boundaries do not nest inside URA or postal boundaries.

### Seed Indonesia geography

```php
use AIArmada\Addressing\Actions\SeedCountryGeographiesAction;

// After address:seed-countries
app(SeedCountryGeographiesAction::class)->execute('ID');
```

Indonesia exposes one administrative hierarchy: `province → regency / city → district → village` (38 provinces, 514 regencies and cities, 7,285 districts, plus 83,762 opt-in villages). Provinces are the ISO 3166-2 states, so the first level resolves through the selected `State`; regencies, districts, and (when seeded) villages are directly assignable. Villages are opt-in via `addressing.geography.indonesia.villages`; postcodes are intentionally not bundled; see `05-country-data.md`.

### Seed Brunei geography

```php
use AIArmada\Addressing\Actions\SeedCountryGeographiesAction;

// After address:seed-countries
app(SeedCountryGeographiesAction::class)->execute('BN');
```

Brunei exposes one administrative hierarchy: `district → mukim` (4 districts, 39 mukims). Districts are the ISO 3166-2 states, so the first level resolves through the selected `State` and only mukims are directly assignable. Villages and postcodes are intentionally not bundled; see `05-country-data.md`.

### Seed Gulf geography

```php
use AIArmada\Addressing\Actions\SeedCountryGeographiesAction;

// After address:seed-countries; repeat per country.
app(SeedCountryGeographiesAction::class)->execute('BH');
```

Bahrain (4 governorates), Qatar (8 municipalities), Kuwait
(6 governorates), and the UAE (7 emirates) each expose a single-level
administrative hierarchy whose values are the ISO 3166-2 states. The
first level resolves through the selected `State`, so there are no
assignable area roles. Qatar and the UAE have no postcode system;
their formatters stack street lines, city, and country. Oman exposes
`governorate → wilayat` (11 governorates, 63 wilayats) via
`execute('OM')`: only governorates link to states, and wilayats are
assignable through the `wilayat` role with their governorate selected
first.

### Seed Levant and North Africa geography

Jordan (12 governorates), Saudi Arabia (13 regions), and Egypt
(27 governorates) each expose a single-level administrative hierarchy
of ISO 3166-2 states, seeded with `execute('JO')`, `execute('SA')`,
and `execute('EG')`. Morocco exposes `region → province / prefecture`
(12 regions, 62 provinces, 13 prefectures) via `execute('MA')`: only
regions link to states, and provinces/prefectures are assignable
through the `province` role with their region selected first.

### Seed South Asia and Türkiye geography

India (28 states + 8 union territories) and Türkiye (81 provinces)
each expose a single-level administrative hierarchy of ISO 3166-2
states, seeded with `execute('IN')` and `execute('TR')`. Pakistan
exposes `province / territory → district` (7 states, 174 districts)
via `execute('PK')`: only provinces/territories link to states, and
districts are assignable through the `district` role with their
province selected first. Bangladesh exposes `division → district`
(8 divisions, 64 districts) via `execute('BD')`: only divisions link
to states, and districts are assignable through the `district` role
with their division selected first. Lower levels (districts of
India/Türkiye — around 780/970 respectively, Pakistani tehsils, and
Bangladeshi upazilas) are intentionally not bundled.

### Seed British and South African geography

The UK exposes a single-level hierarchy of the four nations via
`execute('GB')`; the 221 ISO subdivisions stay global `State` rows
and are not imported as areas. South Africa exposes its nine provinces
via `execute('ZA')`. Both formatters omit the county/province line
when a postcode is present, per the UPU rule.

### Seed East Asia geography

China exposes 33 provincial-level divisions (22 provinces, 5
autonomous regions, 4 municipalities, Hong Kong, Macao) via
`execute('CN')`; Taiwan is its own country, not a CN area. Japan
exposes 47 prefectures via `execute('JP')`. Both are single-level
hierarchies. The Chinese formatter prints `{postcode} {province}`;
the Japanese formatter prints `{city}, {prefecture}` with the
`NNN-NNNN` postcode below.

### Seed European geography

Germany (16 Länder), France (18 regions), Italy (20 regions), Poland
(16 voivodeships), and the Netherlands (12 provinces) each expose a
single-level hierarchy via `execute('DE')`, `execute('FR')`,
`execute('IT')`, `execute('PL')`, and `execute('NL')`. France's 101
departments and Italy's provinces are intentionally not areas (see
`05-country-data.md`). Spain exposes 19 communities/cities → 50
provinces via `execute('ES')`, with provinces assignable through the
`province` role. The Italian formatter takes the province abbreviation
from the optional `province_code` address component.

### Seed United States geography

`execute('US')` imports 50 states, the District of Columbia, and 5
inhabited territories. Military postal regions (`AA`/`AE`/`AP`) and
the Minor Outlying Islands are intentionally not areas. The formatter
renders `{locality} {ST} {ZIP}` per USPS Publication 28, resolving
full state names to abbreviations.

### Seed Russian geography

`execute('RU')` imports the 83 ISO federal subjects (46 oblasts, 21
republics, 9 krais, 4 okrugs, 2 federal cities, 1 autonomous oblast).
Crimea, Sevastopol, and the territories claimed in 2022 are not
ISO-recognized and are absent. The formatter keeps the country last,
per the UPU IB recommendation, deviating from domestic Russian layout.

### Seed African geography, second batch

Nigeria (36 states + FCT), Ethiopia (14 regions/cities),
DR Congo (26 provinces), Tanzania (31 regions), Kenya (47 counties),
Sudan (18 states), Uganda (4 regions), and Algeria (69 wilayas) each
expose a single-level hierarchy via `execute('NG')`, `execute('ET')`,
`execute('CD')`, `execute('TZ')`, `execute('KE')`, `execute('SD')`,
`execute('UG')`, and `execute('DZ')`. Ethiopia's dissolved SNNPR
(`SN`) is deleted on seed; Uganda's volatile districts are
intentionally not bundled.

### Seed Americas geography

Brazil (26 states + DF), Mexico (32 entities), Canada (10 provinces +
3 territories), Argentina (23 provinces + CABA), Colombia (32
departments + Bogotá D.C.), and Peru (25 regions + Lima municipality)
each expose a single-level hierarchy via `execute('BR')`,
`execute('MX')`, `execute('CA')`, `execute('AR')`, `execute('CO')`,
and `execute('PE')`. Brazil, Mexico, Canada, and Australia resolve
state names to abbreviations in the formatter
(`{locality} - {ST}`, `{postcode} {locality}, {abbrev}`,
`{locality} {PR} {postcode}`).

### Seed Asia-Pacific geography, second batch

Australia (6 states + 2 territories), Vietnam (28 provinces + 6
municipalities post-2025 merger), Thailand (76 provinces + Bangkok +
Pattaya), the Philippines (82 provinces), South Korea (17 divisions),
Taiwan (22 divisions), and Ukraine (24 oblasts + Kyiv + Sevastopol +
Crimea) each expose a single-level hierarchy via `execute('AU')`,
`execute('VN')`, `execute('TH')`, `execute('PH')`, `execute('KR')`,
`execute('TW')`, and `execute('UA')`. The Philippines ships provinces
only — its 17 regions stay global states. Australia's formatter uses
double-spaced `{locality}  {ST}  {postcode}`.

### Seed ASEAN remainder geography

Cambodia (24 provinces + Phnom Penh), Laos (17 provinces + Vientiane
Prefecture), and Timor-Leste (14 municipalities) each expose a
single-level hierarchy via `execute('KH')`, `execute('LA')`, and
`execute('TL')`, completing ASEAN at 11 members. Cambodia seeds the
official `Preah Sihanouk` with `Sihanoukville` aliased; Timor-Leste
seeds Atauro under provisional code `AT`. The formatters print
`{province} {postcode}` (KH, 6-digit), `{postcode} {locality}` (LA,
5-digit), and `{locality} {postcode}` (TL, `TL` + 5 digits).

### Seed Asia remainder geography

Armenia (10 regions + Yerevan), Azerbaijan (66 districts + 11
municipalities + Nakhchivan AR), Bhutan (20 dzongkhags), Cyprus (6
districts), Georgia (9 regions + 2 ARs + Tbilisi), Hong Kong (18
districts), Iran (31 provinces), Israel (6 districts), Kazakhstan (17
regions + 3 cities), Kyrgyzstan (7 regions + 2 cities), Lebanon (8
governorates), Maldives (20 atolls + Addu City), Mongolia (21 aimags +
Ulaanbaatar), Nepal (7 provinces), North Korea (9 provinces + 4
cities), Palestine (16 governorates), Sri Lanka (9 provinces + 25
districts flat), Syria (14 provinces), Tajikistan (5 divisions),
Turkmenistan (5 regions + Ashgabat), and Yemen (21 governorates +
Amanat Al Asimah) each expose a single-level hierarchy via
`execute()` with their ISO code, completing Asia coverage alongside
the earlier batches. Hong Kong, North Korea, Syria, and Yemen have no
postcode system; their formatters print any supplied code on its own
line.

### Seed Africa remainder geography

Benin (12 departments), Botswana (10 districts + 2 cities + 4
towns), Burkina Faso (17 regions + 47 provinces flat), Burundi (5
provinces), Cape Verde (22 municipalities + 2 island groups), Central
African Republic (15 prefectures + Bangui + Nana-Grébizi), Chad (23
provinces), Comoros (3 islands), Congo (12 departments), Ivory Coast
(12 districts + 2 autonomous districts), Djibouti (5 regions +
Djibouti City), Equatorial Guinea (2 regions + 8 provinces flat),
Eritrea (6 regions), Gabon (9 provinces), Gambia (5 divisions +
Banjul), Guinea (7 regions + Conakry + 33 prefectures flat),
Guinea-Bissau (3 provinces + 8 regions + Bissau sector), Lesotho (10
districts), Liberia (15 counties), Libya (22 popularates), Malawi (3
regions + 28 districts flat), Mali (10 regions + Bamako), Mauritania
(15 regions), Mauritius (9 districts + 3 dependencies), Namibia (14
regions), Niger (7 regions + Niamey), Rwanda (4 provinces + Kigali),
Sao Tome and Principe (6 districts + Príncipe AR), Senegal (14
regions), Seychelles (27 districts), Sierra Leone (4 provinces +
Western Area), Somalia (18 regions), South Sudan (10 states),
Eswatini (4 regions), Togo (5 regions), Tunisia (24 governorates),
Zambia (10 provinces), and Zimbabwe (10 provinces) each expose a
single-level hierarchy via `execute()` with their ISO code,
completing Africa coverage alongside the earlier batches. Most of
the batch has no postcode system; those formatters print any supplied
code on its own line.

### Seed Europe remainder geography

Albania (12 counties), Andorra (7 parishes), Austria (9 states),
Belarus (6 oblasts + Minsk), Belgium (3 regions + 10 provinces
flat), Bosnia and Herzegovina (2 entities + Brčko District),
Bulgaria (28 districts), Croatia (20 counties + City of Zagreb),
Czech Republic (13
regions + 76 districts + Prague flat), Denmark (5 regions), Estonia
(15 counties + 78 municipalities flat), Finland (18 regions), Greece
(13 regions + Mount Athos), Hungary (20 counties + 22
county-rights cities + Budapest), Iceland (8 regions + 64
municipalities flat), Ireland (4 provinces + 26 counties flat),
Kosovo (7 districts), Latvia (36 municipalities + 7 state cities),
Liechtenstein (11 communes), Lithuania (10 counties + 60
municipalities flat), Luxembourg (12 cantons), Malta (68 local
councils), Moldova (32 districts + 3 cities + 2 units), Monaco (17
quarters), Montenegro (25 municipalities), North Macedonia (80
municipalities), Norway (15 counties + Svalbard/Jan Mayen), Portugal
(18 districts + Azores/Madeira), Romania (41 departments +
Bucharest), San Marino (9 municipalities), Serbia (29 districts + 2
provinces + Belgrade), Slovakia (8 regions), Slovenia (200
municipalities + 12 urban municipalities), Sweden (21 counties),
Switzerland (26 cantons), Aland (16 municipalities), Faroe Islands (6
regions), Guernsey (12 parishes), Jersey (12 parishes), and Isle of
Man (6 sheadings) each expose a single-level hierarchy via
`execute()` with their ISO code, completing Europe coverage
alongside the earlier batches. Every country in the batch has a
postcode system.

### Seed Africa, Central Asia, and Middle East geography

Ghana (16 regions), Angola (18 provinces), Cameroon (10 regions),
Madagascar (6 provinces), Afghanistan (34 provinces), Mozambique (10
provinces + Maputo City), Uzbekistan (12 regions + Karakalpakstan +
Tashkent City), Myanmar (7 regions + 7 states + Naypyidaw), and Iraq
(19 governorates) each expose a single-level hierarchy via
`execute('GH')`, `execute('AO')`, `execute('CM')`, `execute('MG')`,
`execute('AF')`, `execute('MZ')`, `execute('UZ')`, `execute('MM')`,
and `execute('IQ')`. Iraq deletes non-governorate `KR` rows on seed;
Angola's unimplemented 2024 split and Madagascar's codeless regions
are documented in `05-country-data.md`. Angola and Cameroon have no
postcode system.

### Resolve Singapore postcodes

```php
use AIArmada\Addressing\Actions\ResolveSingaporePostalCodesAction;

// Requires ONEMAP_EMAIL and ONEMAP_PASSWORD; seed SG geographies first.
$result = app(ResolveSingaporePostalCodesAction::class)->execute(['569933', '999999']);

$result->resolved; // ['569933']
$result->invalid;  // ['999999']
```

Postcodes already stored in `postal_codes` are served from the database
without an API call. Missing postcodes are looked up on OneMap, persisted
with their canonical address and coordinates, and linked to their postal
sector. Malformed codes and codes OneMap does not know are reported as
invalid; transport and authentication failures throw instead of returning
partial data.

## Search Areas

~~~php
use AIArmada\Addressing\Actions\SearchAddressAreasAction;

$areas = app(SearchAddressAreasAction::class)->execute(
    query: 'KL',
    countryCode: 'MY',
);
~~~

Search supports canonical names, aliases, place type, address role, parent
area and postcode filters.

For incomplete external place-provider results, use the package hierarchy
resolver to locate a named area below a known state root and recover its typed
ancestor. The resolver is provider-agnostic; integrations remain responsible
for translating provider components into the canonical area types and roles.

~~~php
use AIArmada\Addressing\Support\AddressAreaHierarchyResolver;

$subdivision = app(AddressAreaHierarchyResolver::class)->resolveWithinHierarchy(
    name: 'Shah Alam',
    countryId: $country->id,
    hierarchyRootId: $selangorArea->id,
    hierarchyType: 'administrative',
    types: ['city', 'municipality', 'mukim', 'subdistrict'],
);

$district = app(AddressAreaHierarchyResolver::class)->ancestorOfTypes(
    area: $subdivision,
    types: ['district', 'minor_district'],
    hierarchyType: 'administrative',
);

$allAncestors = app(AddressAreaHierarchyResolver::class)->ancestorsOf(
    area: $subdivision,
    hierarchyType: 'administrative',
);
// Nearest first: district, state, ... for this provider's hierarchy.
~~~

## Resolution gaps

External sources (place pickers, geocoders, imports) return names that do not
always match a shipped area name or alias: `Jakarta Pusat` vs `Kota Jakarta
Pusat`, `West Java` vs `Jawa Barat`. Log every attempted-but-unmatched value
so the naming gap is visible instead of silently degrading to a partial match.

~~~php
use AIArmada\Addressing\Actions\LogAddressResolutionGapAction;

app(LogAddressResolutionGapAction::class)->execute(
    source: 'google-picker',
    countryCode: 'ID',
    role: 'regency',
    value: 'Jakarta Pusat',
    reason: 'unmatched', // or 'ambiguous' when several areas could match
    context: ['place_id' => 'ChIJ...', 'attempted' => ['Jakarta Pusat']],
);
~~~

Logging upserts on `(source, country_code, role, normalized)`: repeats bump
`hits` and refresh the `context` sample instead of duplicating rows.
Normalization is minimal (lowercase + collapsed whitespace); provider-specific
translation stays in the integration. Use the `state` role for state-level
misses. Gaps are global reference data with no owner scoping.

A package admin matches open gaps to the correct area (Filament adapter or
action below). Matching creates a `manual` alias, so geography reseeds
preserve it — only provider-sourced names are refreshed on reseed. Gaps never
auto-fill: a failed match cannot know *which* area the string maps to.

~~~php
use AIArmada\Addressing\Actions\IgnoreResolutionGapAction;
use AIArmada\Addressing\Actions\MatchGapToAreaAction;

app(MatchGapToAreaAction::class)->execute($gap, $area, matchedBy: $admin->email);

// Junk values can be ignored; ignored gaps stay terminal on recurrence.
app(IgnoreResolutionGapAction::class)->execute($gap);
~~~

Matching refuses `ambiguous` gaps (they need data cleanup, not another alias),
`state`-role gaps (states have no alias table — fix the integration prefix
rules or rename the provider state), cross-country areas, areas that miss the
role's profile type/level, and values that already resolve to a different area.
Recurrence of a `matched` gap reopens it to `open` as a regression signal.

Report the most-hit gaps and the promotion backlog:

~~~bash
php artisan address:resolution-gaps --country=ID --days=30 --reason=unmatched --status=open --limit=20
~~~

Periodically promote admin-matched aliases back into providers so fresh
installs seed complete data. The export emits copy-paste-ready `areaNames()`
entries grouped per provider file; the paste stays human as the quality gate.
After a reseed lands the provider rows, `--prune` deletes `manual` aliases
exactly duplicated by a provider-sourced pair.

~~~bash
php artisan address:export-gap-aliases --country=ID
php artisan address:export-gap-aliases --country=ID --prune
~~~

## Address area assignments

~~~php
use AIArmada\Addressing\Actions\SyncAddressAreaAssignmentsAction;

app(SyncAddressAreaAssignmentsAction::class)->execute(
    address: $address,
    assignments: [
        'postal_locality' => $bangsar->id,
    ],
    stateId: $address->state_id,
);
~~~

Assignment sync is authoritative: pass the complete current role map. Passing an empty map removes all area assignments from the address. Each selected area is checked against the country profile and its typed containment hierarchy.

Only area-kind roles are accepted as assignment keys. The `state_id` pseudo-role and any other non-area role are rejected — state travels through the `stateId` parameter and the `addresses.state_id` column, never as an assignment. Unknown roles are rejected as not defined by the country profile. All validation runs before any write, so a rejected payload persists nothing.

### Resolving assignment roles

```php
use AIArmada\Addressing\Support\CountryAddressProfileResolver;

$resolver = app(CountryAddressProfileResolver::class);

// Full definition (hierarchy + level) or null.
$definition = $resolver->definitionForRole($countryId, 'administrative_district');

// Level only, for labels and feature checks.
$level = $resolver->levelForRole($countryId, 'state_id');
```

Roles match `$level->assignmentRole ?? "{hierarchyKey}_{levelKey}"` against the first non-state level in hierarchy order. `state_id` resolves to the first state-kind level instead, since those levels carry no assignment role. Unknown countries, unknown roles, and `state_id` for profiles without a state level all return null. The `$country` parameter accepts a country ID, an ISO code, or a country model.

## Import Areas

### From a custom source

```php
use AIArmada\Addressing\Actions\ImportAddressAreasAction;
use AIArmada\Addressing\Support\ArrayAddressAreaSource;
use AIArmada\Addressing\Data\AddressAreaData;

$source = new ArrayAddressAreaSource('my-source', [
    new AddressAreaData(
        source: 'my-source',
        sourceId: '1',
        countryCode: 'MY',
        type: 'state',
        name: 'Selangor',
    ),
]);

$result = app(ImportAddressAreasAction::class)->execute($source);

echo $result->created; // 1
```

### From CSV

```bash
php artisan address:import-areas-csv /path/to/areas.csv --source=my-source
```

CSV format:

```csv
source_id,country_code,type,name,native_name,code,parent_source_id,level,latitude,longitude,hierarchy_type,relationship_type,metadata,source_payload
1,MY,state,Selangor,Selangor,SGR,,1,3.0738,101.5183,,,{},{}
```

Re-imports are idempotent: rows without changes are reported as skipped. Dry-run mode validates every row (including parent and hierarchy checks) without writing. Re-imports never reactivate areas an operator deactivated; pass `--reactivate` (or `reactivate: true`) to opt back in:

```bash
php artisan address:import-areas-csv /path/to/areas.csv --source=my-source --reactivate
```

## HasAddresses Trait

```php
use AIArmada\Addressing\Traits\HasAddresses;

class Customer extends Model
{
    use HasAddresses;
}

$customer = Customer::find(1);
$address = Address::find(1);

$customer->attachAddress($address, type: 'shipping', isPrimary: true);

$primary = $customer->primaryAddress('shipping');
$addresses = $customer->addressesOfType('billing');
```

Address instances and attachment pivots are owner-scoped. Resolve the
addressable and the address inside the same owner context before attaching:

```php
use AIArmada\CommerceSupport\Support\OwnerContext;

OwnerContext::withOwner($owner, function () use ($customer, $address): void {
    $customer->attachAddress($address, type: 'shipping', isPrimary: true);
});
```

Global address records require explicit global context and are not implicitly
shared with tenants.

The customers package uses the shared `addresses()` relation for checkout
hydration and typed billing/shipping defaults. Attach and resolve addresses
inside the same owner context; no package-local address storage or bridge is
required.

Raw queries against `addresses`, `addressables`, or `address_snapshots` must
apply the shared owner query primitive. Reference geography tables are
intentionally global and do not use owner scoping.

> [!info]
> `primaryAddress()` and `addressesOfType()` only consider pivot rows whose `valid_from` / `valid_until` window includes the current time.
>
> Use `scopeWithPrimaryAddress()` when you want to eager-load the current primary subset for display.

## Filter Addressable Models by Location

`AddressLocationScope` applies canonical geography criteria through an addressable relation. It does not assume that the address is primary or that the relation uses a particular validity window.

```php
use AIArmada\Addressing\Data\AddressLocationData;
use AIArmada\Addressing\Support\AddressLocationScope;

$location = AddressLocationData::fromArray([
    'country_id' => $malaysia->id,
    'state_id' => $selangor->id,
    'area_assignments' => [
        'administrative_district' => $petalingDistrict->id,
    ],
]);

$institutions = app(AddressLocationScope::class)
    ->apply(Institution::query(), $location)
    ->get();
```

Pass a relation name when the addressable relation is not named `addresses`.

## Address Snapshots

```php
use AIArmada\Addressing\Actions\CreateAddressSnapshotAction;
use AIArmada\Addressing\Data\AddressData;

$snapshot = app(CreateAddressSnapshotAction::class)->execute(
    snapshotable: $order,
    address: $shippingAddress,
    reason: 'order_placed',
);

// Snapshots are immutable — subsequent changes to the original address
// do not affect existing snapshots.
```

Snapshot reasons are nullable, but a supplied reason must be a non-empty
string. Use stable lowercase identifiers such as `order_shipping`,
`order_placed`, or `event_location`; the value records the domain event that
created the immutable snapshot.

## Formatting

```php
use AIArmada\Addressing\Actions\FormatAddressAction;
use AIArmada\Addressing\Data\AddressData;

$address = AddressData::from([
    'line1' => '123 Jalan Ampang',
    'city' => 'Kuala Lumpur',
    'postcode' => '50450',
    'country' => 'Malaysia',
    'countryCode' => 'MY',
]);

$formatted = app(FormatAddressAction::class)->format($address);
// "123 Jalan Ampang
//  50450 Kuala Lumpur
//  Malaysia"
```

When `countryCode` is present, the action uses the configured country-specific formatter when one is available. Otherwise it uses the generic line-based formatter.

Saving an `Address` regenerates `formatted_address` and `formatted_lines` automatically whenever address inputs change, unless you explicitly set a formatted value on that save. Saves that touch no address inputs skip normalization entirely.

## Normalization

```php
use AIArmada\Addressing\Actions\NormalizeAddressDataAction;

$address = app(NormalizeAddressDataAction::class)->normalize([
    'street_address' => '123 Jalan Ampang',
    'zip_code' => '50450',
]);

echo $address->line1; // "123 Jalan Ampang"
echo $address->postcode; // "50450"
```

## AddressDataCast

```php
use AIArmada\Addressing\Casts\AddressDataCast;

protected function casts(): array
{
    return [
        'shipping_address' => AddressDataCast::class,
    ];
}
```

This allows JSON columns to be cast to/from `AddressData` objects.

## Navigation Links

Navigation links let you store manual Google Maps and Waze URLs on addresses. Manual links always win over generated links. Stored URLs must be valid `http`/`https` URLs; anything else is discarded on write.

### Storing Links

```php
use AIArmada\Addressing\Data\AddressData;

$address = AddressData::from([
    'line1' => 'Jalan Tuanku Abdul Halim',
    'city' => 'Kuala Lumpur',
    'countryCode' => 'MY',
    'google_maps_url' => 'https://maps.app.goo.gl/example',
    'waze_url' => 'https://waze.com/ul?ll=3.1712,101.6678&navigate=yes',
]);
```

### Building Navigation Links

```php
use AIArmada\Addressing\Actions\BuildAddressNavigationLinksAction;

$links = app(BuildAddressNavigationLinksAction::class)->execute($address);

echo $links['google_maps_url']; // manual link or generated fallback
echo $links['google_maps_source']; // 'manual' | 'navigation_links' | 'generated_place_id' | 'generated_coordinates' | 'generated_formatted_address'
echo $links['waze_url'];
echo $links['waze_source'];
```

### Priority Rules

**Google Maps:** manual → `navigation_links.google_maps.url` → Place ID → coordinates → formatted address → null

**Waze:** manual → `navigation_links.waze.url` → coordinates → formatted address → null

### Snapshots

Navigation links are copied into snapshots and preserved even when the original address is later edited.

## Location Slug Segments

`LocationSlugSegments` resolves canonical location names from the global geography reference data so hosts can build location-disambiguated slugs (`grand-hall-kuala-lumpur-my`) from canonical names rather than free-text input:

```php
use AIArmada\Addressing\Support\LocationSlugSegments;

$suffix = LocationSlugSegments::suffix(
    ['city' => 'Kuala Lumpur', 'state_id' => $stateId, 'country_id' => $countryId],
    cityAreaId: $subdivisionAreaId,
    stateAreaId: $districtAreaId,
);
// 'kuala-lumpur-wilayah-persekutuan-my'
```

Each level prefers the literal address string, then the canonical name for the referenced geography id, then the assigned area name; consecutive duplicates collapse to one. Pass `preferLiteralCountry: false` when the referenced country's ISO code should win over the literal `country_code`. Granular resolvers (`areaName()`, `cityName()`, `stateName()`, `countryCode()`) are available when you need a single level.
