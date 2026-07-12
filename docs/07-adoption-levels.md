---
title: Adoption Levels
---

# Adoption Levels

## Purpose

This document defines exact adoption levels for packages using `aiarmada/addressing`.

Agents must assign one level before editing a package.

## Level 0: No adoption

Use when the package does not handle address-like data.

Examples:

- packages that only handle UI theme
- packages that only handle unrelated content
- packages where `country` means localization and not address

Do not add `aiarmada/addressing` dependency.

## Level 1: AddressData only

Use when a package needs a normalized address value but does not need shared storage.

Typical uses:

- provider payload mapping
- tax context normalization
- gateway adapter input
- converting legacy fields into one shape

Example:

```php
use AIArmada\Addressing\Data\AddressData;

$address = AddressData::from([
    'street_address' => $client->street_address,
    'city' => $client->city,
    'state' => $client->state,
    'zip_code' => $client->zip_code,
    'countryCode' => $client->country_code,
]);
```

No migration required.

## Level 2: AddressData plus mapper/contracts

Use when a package talks to external providers or package-level interfaces.

Typical uses:

- JNT payload mapper
- Chip payload mapper
- cashier gateway billing payload
- commerce-support billing/shipping contracts

Example:

```php
use AIArmada\Addressing\Data\AddressData;

final class JntAddressMapper
{
    /**
     * @return array{address: string|null, city: string|null, state: string|null, postCode: string|null, countryCode: string|null}
     */
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

No storage migration required.

## Level 3: AddressData JSON cast or snapshot

Use when the package stores historical or transaction-bound address data.

Typical uses:

- shipment origin/destination address JSON
- event published location snapshot
- order billing/shipping address snapshot
- gateway transaction address payload

Example JSON cast:

```php
use AIArmada\Addressing\Casts\AddressDataCast;
use Illuminate\Database\Eloquent\Model;

final class Shipment extends Model
{
    protected function casts(): array
    {
        return [
            'origin_address' => AddressDataCast::class,
            'destination_address' => AddressDataCast::class,
        ];
    }
}
```

Storage may remain package-local.

## Level 4: Address model and HasAddresses

Use when the package owns reusable, editable addresses.

Typical uses:

- customer saved address
- institution address
- masjid address
- venue address
- branch address

Example:

```php
use AIArmada\Addressing\Traits\HasAddresses;
use Illuminate\Database\Eloquent\Model;

final class Customer extends Model
{
    use HasAddresses;
}
```

Create address:

```php
use AIArmada\Addressing\Actions\CreateAddressAction;
use AIArmada\Addressing\Data\AddressData;

app(CreateAddressAction::class)->execute(
    addressable: $customer,
    data: AddressData::from([
        'line1' => 'Lot 12 Jalan Mawar',
        'city' => 'Kajang',
        'state' => 'Selangor',
        'postcode' => '43000',
        'countryCode' => 'MY',
    ]),
    type: 'shipping',
    isPrimary: true,
);
```

This level may eventually replace legacy reusable address tables.

## Level 5: Storage migration and cleanup

Use only after a package has already adopted Level 4 or Level 3 and tests prove new reads/writes are correct.

Actions:

1. Copy old data to new storage.
2. Update reads and writes.
3. Add regression tests.
4. Deploy safely.
5. Remove old columns/tables in a dedicated cleanup migration.

Do not combine data-copy and deletion unless explicitly approved.

## Level selection matrix

| Address meaning | Adoption level |
|---|---:|
| Simple provider payload | Level 1 or 2 |
| Gateway adapter billing address | Level 2 |
| Shipment origin/destination JSON | Level 3 |
| Order billing/shipping address | Level 3 |
| Event published address | Level 3 |
| Customer saved address | Level 4, then Level 5 |
| Venue/institution address | Level 4, then Level 5 |
| Config company/vendor address string | Level 1 optional |
| IP resolved location | Do not use full AddressData; use ResolvedLocationData |

## Recommended monorepo rollout

### First pass

Use Level 1 and Level 2 to standardize names without migrations.

### Second pass

Use Level 3 for historical address snapshots and JSON casts.

### Third pass

Use Level 4 for customers, institutions, venues, and other reusable address owners.

### Final pass

Use Level 5 cleanup only after tests and docs prove all behavior moved.
