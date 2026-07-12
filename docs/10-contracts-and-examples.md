---
title: Contracts and Examples
---

# Contracts and Examples

## Purpose

This document provides copy-paste-ready examples for consuming packages.

## Canonical AddressData input

Use this shape for most package boundaries:

```php
use AIArmada\Addressing\Data\AddressData;

$address = AddressData::from([
    'line1' => 'Lot 12 Jalan Mawar',
    'line2' => 'Taman Bahagia',
    'line3' => null,
    'city' => 'Kajang',
    'district' => 'Hulu Langat',
    'state' => 'Selangor',
    'postcode' => '43000',
    'countryCode' => 'MY',
]);
```

## Supported legacy/provider aliases

`AddressData::from()` or `AddressNormalizer` should understand these aliases:

```txt
address_line_1 -> line1
address_line_2 -> line2
street_address -> line1
shipping_street_address -> line1
postal_code -> postcode
zip_code -> postcode
postCode -> postcode
country_code -> countryCode
countryCode -> countryCode
country -> countryCode when the value is a 2-letter ISO code
```

## Customer saved address example

```php
namespace AIArmada\Customers\Actions;

use AIArmada\Addressing\Actions\CreateAddressAction;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Customers\Models\Customer;

final class StoreCustomerShippingAddressAction
{
    public function __construct(
        private readonly CreateAddressAction $createAddress,
    ) {}

    /**
     * @param array{line1?: string|null, line2?: string|null, city?: string|null, state?: string|null, postcode?: string|null, countryCode?: string|null} $input
     */
    public function execute(Customer $customer, array $input): void
    {
        $this->createAddress->execute(
            addressable: $customer,
            data: AddressData::from($input),
            type: 'shipping',
            isPrimary: true,
        );
    }
}
```

## Order snapshot example

```php
namespace AIArmada\Orders\Actions;

use AIArmada\Addressing\Actions\CreateAddressSnapshotAction;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Orders\Models\Order;

final class SnapshotOrderShippingAddressAction
{
    public function __construct(
        private readonly CreateAddressSnapshotAction $createAddressSnapshot,
    ) {}

    public function execute(Order $order, AddressData $shippingAddress): void
    {
        $this->createAddressSnapshot->execute(
            snapshotable: $order,
            data: $shippingAddress,
            reason: 'order_shipping',
        );
    }
}
```

## Shipment cast example

```php
namespace AIArmada\Shipping\Models;

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

## Event location resolver example

```php
namespace MajlisIlmu\Events\Actions;

use AIArmada\Addressing\Data\AddressData;
use MajlisIlmu\Events\Models\Event;

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

        if (filled($event->manual_location_text)) {
            return AddressData::fromFormatted($event->manual_location_text);
        }

        return null;
    }
}
```

## Event publication snapshot example

```php
namespace MajlisIlmu\Events\Actions;

use AIArmada\Addressing\Actions\CreateAddressSnapshotAction;
use MajlisIlmu\Events\Models\Event;

final class SnapshotEventLocationAction
{
    public function __construct(
        private readonly ResolveEventAddressAction $resolveEventAddress,
        private readonly CreateAddressSnapshotAction $createAddressSnapshot,
    ) {}

    public function execute(Event $event): void
    {
        $address = $this->resolveEventAddress->execute($event);

        if ($address === null) {
            return;
        }

        $this->createAddressSnapshot->execute(
            snapshotable: $event,
            data: $address,
            reason: 'event_location',
        );
    }
}
```

## Chip billing/shipping mapper

```php
namespace AIArmada\Chip\Support;

use AIArmada\Addressing\Data\AddressData;
use AIArmada\Chip\Models\ChipClient;

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

    public function shippingAddress(ChipClient $client): AddressData
    {
        return AddressData::from([
            'line1' => $client->shipping_street_address,
            'city' => $client->shipping_city,
            'state' => $client->shipping_state,
            'postcode' => $client->shipping_zip_code,
            'countryCode' => $client->shipping_country,
        ]);
    }
}
```

## JNT mapper

```php
namespace AIArmada\Jnt\Support;

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

## Cashier gateway mapper

```php
namespace AIArmada\Cashier\Support;

use AIArmada\Addressing\Data\AddressData;

final class CashierAddressPayloadMapper
{
    /**
     * @return array{line1: string|null, line2: string|null, city: string|null, state: string|null, postal_code: string|null, country: string|null}
     */
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

## Tax resolver

```php
namespace AIArmada\Tax\Actions;

use AIArmada\Addressing\Data\AddressData;

final class ResolveTaxAddressAction
{
    /**
     * @param array<string, mixed> $context
     */
    public function execute(array $context): ?AddressData
    {
        foreach (['shipping_address', 'billing_address', 'address'] as $key) {
            if (isset($context[$key]) && is_array($context[$key])) {
                return AddressData::from($context[$key]);
            }
        }

        return null;
    }
}
```

## Config structured address normalization

```php
use AIArmada\Addressing\Data\AddressData;

$value = config('docs.company.address_data');

$address = is_array($value)
    ? AddressData::from($value)
    : null;
```

## Resolved location data for signals

```php
namespace AIArmada\Signals\Data;

final class ResolvedLocationData
{
    public function __construct(
        public readonly ?string $countryCode,
        public readonly ?string $countryName,
        public readonly ?string $state,
        public readonly ?string $city,
        public readonly ?string $postcode,
        public readonly ?string $formatted,
        public readonly ?string $ipAddress,
    ) {}
}
```

## Relationship expectations

`HasAddresses` should be used only on models that own reusable addresses:

```php
use AIArmada\Addressing\Traits\HasAddresses;

final class Institution extends Model
{
    use HasAddresses;
}
```

Do not add `HasAddresses` to order snapshots, shipment snapshots, gateway payloads, or IP signal sessions.

## Formatting example

```php
use AIArmada\Addressing\Actions\FormatAddressAction;

$formatted = app(FormatAddressAction::class)->execute($addressData);
```

Output may be:

```txt
Lot 12 Jalan Mawar
Taman Bahagia
43000 Kajang
Selangor
Malaysia
```
