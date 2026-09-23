---
title: Installation
---

# Installation

## Requirements

- PHP 8.4+
- Laravel 13+
- `aiarmada/commerce-support`

## Install

```bash
composer require aiarmada/addressing
```

## Publish Configuration

```bash
php artisan vendor:publish --tag=address-config
```

## Run Migrations

Migrations run automatically via the service provider. To publish them first:

```bash
php artisan vendor:publish --tag=address-migrations
php artisan migrate
```

## Seed the Reference Dataset

```bash
php artisan address:seed
```

This is the single entry point. It seeds countries, currency/timezone
references, states, cities, then every configured country geography provider,
with progress bars. From your app's `DatabaseSeeder`, call the bundled seeder
instead of orchestrating the steps yourself:

```php
use AIArmada\Addressing\Database\Seeders\AddressingSeeder;

$this->call(AddressingSeeder::class);
```

For custom orchestration, call the action directly. Without a callback it runs
silently, which is what queued jobs and tests want:

```php
use AIArmada\Addressing\Actions\SeedAddressingAction;
use AIArmada\Addressing\Support\ConsoleSeedProgress;

app(SeedAddressingAction::class)->execute(
    ConsoleSeedProgress::for($this->command?->getOutput()),
);
```

The granular commands remain for partial work: `address:seed-countries`,
`address:seed-country-references`, `address:seed-states`, `address:seed-cities`,
and `address:seed-geographies {country?}`.

## Seed a Country Geography Provider

```php
use AIArmada\Addressing\Actions\SeedCountryGeographiesAction;

app(SeedCountryGeographiesAction::class)->execute('MY');
```

The `address:seed-geographies` command always shows progress.

The bundled Malaysia provider populates State/Federal Territory rows, imports its AddressArea hierarchy, and creates explicit State↔AddressArea links. Its address model exposes separate postal/address and administrative/land hierarchies. The bundled Singapore provider works the same way with `execute('SG')`: five CDC-district states, the URA planning tree, and the postal district/sector tree. The bundled Indonesia provider works the same way with `execute('ID')`: 38 province states and the province → regency/city → district tree. The bundled Brunei provider works the same way with `execute('BN')`: four district states and the district → mukim tree. Every other bundled provider works the same way with its own ISO2 code — see [05-country-data](05-country-data.md) for the full per-country list. Add another provider class to `addressing.geography.providers` for another country; the core tables do not change.
