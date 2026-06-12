---
title: Package Playbooks
---

# Package Playbooks

## Purpose

This document gives package-by-package adoption guidance for the known monorepo surfaces.

Each playbook answers:

- Should the package use `aiarmada/addressing`?
- Should it hard-require the package?
- Should it remove existing address columns/tables?
- What level should it adopt first?
- What code pattern should it use?

## Summary table

| Package/surface | First adoption level | Storage migration? | Notes |
|---|---:|---|---|
| customers / customer_addresses | Level 4 | Eventually yes | Reusable saved addresses |
| orders / order_addresses | Level 3 | Maybe | Historical snapshots, not mutable addresses |
| events / venues / event_locations | Level 4 + Level 3 | Eventually for venues | Venue/institution address reusable; event location snapshot historical |
| chip / chip_clients | Level 2 | Not first | Provider/client mapper |
| shipping / shipments JSON | Level 3 | No | JSON cast is already suitable |
| signals / signal_sessions | Resolver only | No | Approximate IP/location, not full address |
| jnt data object | Level 2 | No | Mapper only |
| commerce-support | Level 2 contracts | No direct storage | Shared interfaces may return AddressData |
| cashier | Level 2 | No unless saved billing profiles | Gateway payload mapper |
| tax | Level 1 | No | Partial address for jurisdiction |
| docs config company.address | Optional Level 1 | No | Keep string support |
| cashier-chip config vendor_address | Optional Level 1 or 2 | No | Keep string support |

## Customers

### Current shape

```txt
customer_addresses
- line1
- line2
- city
- state
- postcode
- country
```

### Recommendation

Use Level 4 first.

Customer saved addresses are reusable and mutable. They are strong candidates for `Address` + `addressables`.

### Should customers require addressing?

Yes, if customers will use shared address storage.

### Should `customer_addresses` be deleted?

Eventually, but not in the first pass.

Migration order:

1. Add `HasAddresses` to `Customer`.
2. Add conversion from `customer_addresses` rows to `AddressData`.
3. Create data-copy migration to `addresses` + `addressables`.
4. Update read paths.
5. Update write paths.
6. Add tests for billing/shipping/primary address behavior.
7. Remove old table only after all usage is migrated.

### Example

```php
use AIArmada\Addressing\Actions\CreateAddressAction;
use AIArmada\Addressing\Data\AddressData;

app(CreateAddressAction::class)->execute(
    addressable: $customer,
    data: AddressData::from($request->validated('shipping_address')),
    type: 'shipping',
    isPrimary: true,
);
```

## Orders

### Current shape

```txt
order_addresses
- line1
- line2
- city
- state
- postcode
- country
```

### Recommendation

Use Level 3.

Order addresses are historical and must not depend only on mutable customer address records.

### Should orders require addressing?

Yes if `AddressSnapshot` or `AddressData` is used directly.

### Should `order_addresses` be deleted?

Not automatically. The table may already act as a snapshot table.

Possible options:

1. Keep `order_addresses`, but expose `toAddressData()`.
2. Migrate to `address_snapshots` if shared snapshot infrastructure is desired.
3. Use JSON columns if order address structure is simple and internal.

### Example

```php
use AIArmada\Addressing\Actions\CreateAddressSnapshotAction;
use AIArmada\Addressing\Data\AddressData;

app(CreateAddressSnapshotAction::class)->execute(
    snapshotable: $order,
    data: AddressData::from($customer->primaryAddressOfType('shipping')),
    reason: 'order_shipping',
);
```

## Events, venues, and institutions

### Current shape

```txt
venues
- address_line_1
- address_line_2
- city
- district
- state
- postcode
- country

event_locations
- address_snapshot
```

### Recommendation

Use both:

```txt
Venue / Institution -> Level 4
Event location      -> Level 3
```

Venue and institution addresses are reusable. Event locations are historical snapshots.

### Should events require addressing?

Yes for location snapshots and address resolution.

### Should venue columns be removed?

Eventually, after venue/institution addresses are migrated to `addresses` + `addressables`.

### Should `event_locations.address_snapshot` be removed?

Not necessarily. It may remain as JSON cast to `AddressData` or migrate to `address_snapshots`.

### Event address resolution order

The events package owns this logic:

```txt
1. Event-specific venue/address override
2. Event venue primary address
3. Event institution/masjid primary address
4. Manual location text
5. Online/unknown
```

### Example resolver

```php
use AIArmada\Addressing\Data\AddressData;

final class ResolveEventAddressAction
{
    public function execute(Event $event): ?AddressData
    {
        if ($event->venue?->primaryAddress() !== null) {
            return AddressData::from($event->venue->primaryAddress());
        }

        if ($event->institution?->primaryAddress() !== null) {
            return AddressData::from($event->institution->primaryAddress());
        }

        return null;
    }
}
```

## Chip

### Current shape

```txt
chip_clients
- street_address
- shipping_street_address
- city
- shipping_city
- state
- shipping_state
- zip_code
- shipping_zip_code
- country
- shipping_country
```

### Recommendation

Use Level 2.

Chip should map between its provider/client fields and `AddressData`.

### Should Chip remove columns?

No in the first pass. These fields may mirror provider payload needs.

### Example

```php
use AIArmada\Addressing\Data\AddressData;

final class ChipClientAddressMapper
{
    public function billingAddress(ChipClient $client): AddressData
    {
        return AddressData::from([
            'line1' => $client->street_address,
            'city' => $client->city,
            'state' => $client->state,
            'postcode' => $client->zip_code,
            'countryCode' => $client->country,
        ]);
    }
}
```

## Shipping

### Current shape

```txt
shipments
- origin_address JSON
- destination_address JSON
```

### Recommendation

Use Level 3.

Keep JSON storage and cast both fields to `AddressData`.

### Should shipping remove columns?

No. These JSON columns are appropriate snapshot storage.

### Example

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

## Signals

### Current shape

```txt
signal_sessions
- country
- resolved_country_code
- resolved_country_name
- resolved_state
- resolved_city
- resolved_postcode
- resolved_formatted_address
- ip_address
```

### Recommendation

Do not use full `AddressData` as the main model.

Use country/area resolver support only. Consider `ResolvedLocationData`.

### Why?

IP geolocation is approximate and not a postal address.

### Should signals require addressing?

Optional. If it only resolves country names/codes, it may use addressing when available.

## JNT

### Current shape

```txt
address
city
state
postCode
countryCode
```

### Recommendation

Use Level 2.

No DB changes needed.

### Example

```php
use AIArmada\Addressing\Data\AddressData;

final class JntAddressMapper
{
    public function toPayload(AddressData $address): array
    {
        return [
            'address' => $address->line1,
            'city' => $address->city,
            'state' => $address->state,
            'postCode' => $address->postcode,
            'countryCode' => $address->countryCode,
        ];
    }
}
```

## Commerce-support

### Recommendation

Use Level 2 for shared contracts only if address contracts are truly shared across commerce packages.

Possible interfaces:

```php
use AIArmada\Addressing\Data\AddressData;

interface HasBillingAddress
{
    public function billingAddressData(): ?AddressData;
}

interface HasShippingAddress
{
    public function shippingAddressData(): ?AddressData;
}
```

### Caution

Because `commerce-support` is foundational, do not add a hard dependency unless the monorepo intentionally makes addressing a foundation package too.

Alternative: define lightweight interfaces in commerce-support and implement adapters in address-aware packages.

## Cashier

### Recommendation

Use Level 2.

Cashier should normalize to `AddressData`, then map to gateway payload fields.

### Example

```php
use AIArmada\Addressing\Data\AddressData;

final class CashierAddressPayloadMapper
{
    public function toGatewayPayload(AddressData $address): array
    {
        return [
            'line1' => $address->line1,
            'line2' => $address->line2,
            'city' => $address->city,
            'state' => $address->state,
            'postal_code' => $address->postcode,
            'country' => $address->countryCode,
        ];
    }
}
```

## Tax

### Recommendation

Use Level 1.

Tax usually needs country/state/postcode. Use partial `AddressData` unless a dedicated `TaxAddressData` becomes necessary.

### Example

```php
use AIArmada\Addressing\Data\AddressData;

$address = AddressData::from([
    'countryCode' => $context['shipping_address']['country'] ?? null,
    'state' => $context['shipping_address']['state'] ?? null,
    'postcode' => $context['shipping_address']['postcode'] ?? null,
]);
```

## Docs

### Current shape

```txt
config key: company.address string
```

### Recommendation

Keep string support. Optionally support structured address arrays.

### Example

```php
'company' => [
    'address' => 'Unit 1, Shah Alam, Selangor, Malaysia',
    'address_data' => [
        'line1' => 'Unit 1',
        'city' => 'Shah Alam',
        'state' => 'Selangor',
        'postcode' => '40100',
        'countryCode' => 'MY',
    ],
],
```

## Cashier-chip

### Current shape

```txt
config key: vendor_address
```

### Recommendation

Keep string support. Optionally normalize structured arrays through `AddressData`.

Do not add address tables.

## Final warning

Do not copy one package's address storage pattern into every package. Address meaning decides the adoption level.

If unsure, start with `AddressData` only. It is the smallest safe step.
