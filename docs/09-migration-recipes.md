---
title: Migration Recipes
---

# Migration Recipes

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

## Recipe 2: Customer addresses to addressables

Use when converting reusable customer saved addresses.

### Before

```txt
customer_addresses
- id
- customer_id
- type
- line1
- line2
- city
- state
- postcode
- country
```

### After

```txt
addresses
addressables
```

### Data-copy action

Create an Action instead of stuffing logic into a migration closure when the logic is non-trivial.

```php
namespace AIArmada\Customers\Actions;

use AIArmada\Addressing\Actions\CreateAddressAction;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Customers\Models\CustomerAddress;

final class MigrateCustomerAddressToAddressingAction
{
    public function __construct(
        private readonly CreateAddressAction $createAddress,
    ) {}

    public function execute(CustomerAddress $legacyAddress): void
    {
        $this->createAddress->execute(
            addressable: $legacyAddress->customer,
            data: AddressData::from([
                'line1' => $legacyAddress->line1,
                'line2' => $legacyAddress->line2,
                'city' => $legacyAddress->city,
                'state' => $legacyAddress->state,
                'postcode' => $legacyAddress->postcode,
                'countryCode' => $legacyAddress->country,
            ]),
            type: $legacyAddress->type ?? 'primary',
            isPrimary: (bool) $legacyAddress->is_primary,
        );
    }
}
```

### Checklist

- Preserve `type` such as billing/shipping.
- Preserve primary/default flags.
- Preserve recipient/contact metadata if present.
- Do not duplicate on rerun; use a migration marker or deterministic metadata.
- Add tests for old customer address accessors.

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
