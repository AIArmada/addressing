---
title: Migration Recipes
---

# Migration Recipes

## Current identity and lineage rules

`addressing.database.tables.*` is the only table-name configuration source.
Every runtime surface and migration resolves names through
`AddressingTableResolver`, so a customized map is applied consistently from
initial schema creation through later migrations.

Customer address adoption is complete: reads and writes use canonical
`addresses` and `addressables`, with no copy migration, backfill, or
dual-read bridge.

## Purpose

This document gives migration recipes for adopting `aiarmada/addressing` without accidental data loss.

## Golden rule

Do not remove old address columns or tables during the first adoption pass.

Use this sequence:

```txt
1. Introduce AddressData conversion
2. Add tests around existing behavior
3. Add new storage or casts
4. Copy data
5. Switch reads
6. Switch writes
7. Verify
8. Remove legacy storage in a separate cleanup migration
```

If a package is not production yet, breaking changes may be allowed, but still write migrations carefully and make data movement explicit.

## Recipe 1: Legacy row to AddressData

Use when the existing table remains for now.

```php
use AIArmada\Addressing\Data\AddressData;

public function toAddressData(): AddressData
{
    return AddressData::from([
        'line1' => $this->line1,
        'line2' => $this->line2,
        'city' => $this->city,
        'state' => $this->state,
        'postcode' => $this->postcode,
        'countryCode' => $this->country,
    ]);
}
```

For events legacy names:

```php
return AddressData::from([
    'address_line_1' => $this->address_line_1,
    'address_line_2' => $this->address_line_2,
    'city' => $this->city,
    'district' => $this->district,
    'state' => $this->state,
    'postcode' => $this->postcode,
    'countryCode' => $this->country,
]);
```

## Recipe 2: Customer address storage retirement

Use after every customer read and write has moved to canonical
`Address::addresses()` and `primaryAddress()` APIs.

The cleanup migration should:

1. Preflight the configured table and one or more known package-local columns.
2. Remove non-primary indexes before dropping the table.
3. Drop the table only when the preflight identifies the retired shape.
4. Avoid backfills and rollback paths; development databases are
   delete-and-rerun.

Verify the migration twice on a development database and confirm that
canonical address rows and addressable pivots remain available.

## Recipe 3: Venue address columns to Address

Use when migrating venue/institution addresses.

### Before

```txt
venues.address_line_1
venues.address_line_2
venues.city
venues.district
venues.state
venues.postcode
venues.country
```

### After

```txt
venues use HasAddresses
```

### Copy example

```php
use AIArmada\Addressing\Actions\CreateAddressAction;
use AIArmada\Addressing\Data\AddressData;

app(CreateAddressAction::class)->execute(
    addressable: $venue,
    data: AddressData::from([
        'address_line_1' => $venue->address_line_1,
        'address_line_2' => $venue->address_line_2,
        'city' => $venue->city,
        'district' => $venue->district,
        'state' => $venue->state,
        'postcode' => $venue->postcode,
        'countryCode' => $venue->country,
    ]),
    type: 'venue',
    isPrimary: true,
);
```

### Keep event snapshots separate

Do not replace event location snapshots with live venue address. When publishing/approving an event, snapshot the resolved address.

## Recipe 4: Order address to snapshot

If `order_addresses` already represents immutable order-time data, do not rush to remove it.

Option A: keep table and expose `AddressData`.

Option B: copy to shared `address_snapshots`.

### Shared snapshot example

```php
use AIArmada\Addressing\Actions\CreateAddressSnapshotAction;
use AIArmada\Addressing\Data\AddressData;

app(CreateAddressSnapshotAction::class)->execute(
    snapshotable: $order,
    data: AddressData::from($legacyOrderAddress->toArray()),
    reason: 'order_shipping',
);
```

## Recipe 5: Shipment JSON cast

Use when the package already has JSON columns.

### Migration

No migration required if columns already exist and contain compatible JSON.

If adding a new JSON column, use the package JSON column type config in that package.

### Model

```php
use AIArmada\Addressing\Casts\AddressDataCast;

protected function casts(): array
{
    return [
        'origin_address' => AddressDataCast::class,
        'destination_address' => AddressDataCast::class,
    ];
}
```

## Recipe 6: Provider payload mapper

Use when the provider needs field names that differ from canonical address names.

```php
use AIArmada\Addressing\Data\AddressData;

final class ProviderAddressMapper
{
    public function toPayload(AddressData $address): array
    {
        return [
            'street_address' => $address->line1,
            'city' => $address->city,
            'state' => $address->state,
            'zip_code' => $address->postcode,
            'country' => $address->countryCode,
        ];
    }
}
```

Do not leak provider names back into the domain.

## Recipe 7: Config string to optional structured address

Keep string support:

```php
'vendor_address' => 'Unit 1, Kuala Lumpur, Malaysia',
```

Add optional structured support:

```php
'vendor_address_data' => [
    'line1' => 'Unit 1',
    'city' => 'Kuala Lumpur',
    'state' => 'Wilayah Persekutuan Kuala Lumpur',
    'postcode' => '50480',
    'countryCode' => 'MY',
],
```

Render:

```php
$address = is_array(config('cashier-chip.vendor_address_data'))
    ? AddressData::from(config('cashier-chip.vendor_address_data'))
    : null;
```

## Migration safety checklist

Before running a data migration:

- Confirm source table and columns.
- Confirm target table and expected rows.
- Confirm tenant/owner scoping if the data is tenant-owned.
- Confirm null handling.
- Confirm country code normalization.
- Confirm idempotency.
- Confirm old behavior has tests.
- Confirm new behavior has tests.
- Confirm no DB FK constraints/cascades are added.
- Confirm package docs are updated.

## Verification commands

Run only affected package checks.

```bash
./vendor/bin/pest --parallel packages/<pkg>/tests
./vendor/bin/phpstan analyse packages/<pkg>/src --level=6
./vendor/bin/pint packages/<pkg>/src packages/<pkg>/tests
```

If a migration touches core addressing too, run both packages' relevant tests.
