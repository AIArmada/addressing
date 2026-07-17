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

## Seed Country Data

```php
use AIArmada\Addressing\Actions\SeedAddressCountriesAction;

app(SeedAddressCountriesAction::class)->execute();
// ['created' => 249, 'updated' => 0, 'skipped' => 0]
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

### Seed Malaysia geography

```php
use AIArmada\Addressing\Actions\SeedCountryGeographiesAction;

// After address:seed-countries
app(SeedCountryGeographiesAction::class)->execute('MY');
```

This uses the bundled Malaysia country provider. Other country providers can define different address structures without changing `State`, `City`, or `AddressArea` core tables.

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
source_id,country_code,type,name,native_name,code,parent_source_id,level,latitude,longitude
1,MY,state,Selangor,Selangor,SGR,,1,3.0738,101.5183
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
$customer->setPrimaryAddress($address, type: 'shipping');

$primary = $customer->primaryAddress('shipping');
$addresses = $customer->addressesOfType('billing');
```

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
    'admin_area_1_id' => $petalingDistrict->id,
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

## Formatting

```php
use AIArmada\Addressing\Actions\FormatAddressAction;
use AIArmada\Addressing\Data\AddressData;

$address = AddressData::from([
    'line1' => '123 Jalan Ampang',
    'city' => 'Kuala Lumpur',
    'postcode' => '50450',
    'country' => 'Malaysia',
]);

$formatted = app(FormatAddressAction::class)->format($address);
// "123 Jalan Ampang
//  50450 Kuala Lumpur
//  Malaysia"
```

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

Navigation links let you store manual Google Maps and Waze URLs on addresses. Manual links always win over generated links.

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
