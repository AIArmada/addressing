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

## Seed Country Data

```bash
php artisan address:seed-countries
```

This imports the bundled ISO 3166-1 country/territory data into the configured countries table (default `countries`).

## Seed a Country Geography Provider

```php
use AIArmada\Addressing\Actions\SeedCountryGeographiesAction;

app(SeedCountryGeographiesAction::class)->execute('MY');
```

The bundled Malaysia provider populates State/City rows, imports its AddressArea hierarchy, and creates explicit State↔AddressArea links. Add another provider class to `addressing.geography.providers` for another country; the core tables do not change.
