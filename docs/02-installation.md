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

## Optional: Seed Malaysia States and Cities

```php
use AIArmada\Addressing\Database\Seeders\MalaysiaGeographySeeder;

$this->call(MalaysiaGeographySeeder::class);
```

Or from the application database seeder / artisan tinker. This populates first-class `State` and `City` rows for Malaysia after countries are seeded.
