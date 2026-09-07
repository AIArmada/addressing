<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class AddressCountryResolver
{
    public function resolve(mixed $country): ?Model
    {
        $countryClass = ModelResolver::countryClass();

        if ($country instanceof $countryClass) {
            return $country;
        }

        if (! is_scalar($country)) {
            return null;
        }

        $value = mb_trim((string) $country);

        if ($value === '') {
            return null;
        }

        /** @var Model $countryModel */
        $countryModel = new $countryClass;

        if (! Schema::hasTable($countryModel->getTable())) {
            return null;
        }

        $query = $countryClass::query();

        if (Str::isUuid($value)) {
            return $query->whereKey($value)->first();
        }

        if (preg_match('/^[A-Za-z]{2}$/', $value) === 1) {
            return $query->where('iso2', mb_strtoupper($value))->first();
        }

        return null;
    }

    public function resolveId(mixed $country): ?string
    {
        $key = $this->resolve($country)?->getKey();

        return $key === null ? null : (string) $key;
    }

    public function timezoneFor(mixed $country): ?string
    {
        $resolved = $this->resolve($country);

        if (! $resolved instanceof Model || ! method_exists($resolved, 'timezones')) {
            return null;
        }

        $timezone = $resolved->timezones()->value('name');

        if (is_string($timezone)) {
            $timezone = $this->normalizeTimezone($timezone);

            if ($timezone !== null) {
                return $timezone;
            }
        }

        return null;
    }

    private function normalizeTimezone(string $timezone): ?string
    {
        $timezone = mb_trim($timezone);

        if ($timezone === '') {
            return null;
        }

        if (preg_match('/^UTC([+-](?:0\d|1[0-4]):[0-5]\d)$/', $timezone, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match('/^[+-](?:0\d|1[0-4]):[0-5]\d$/', $timezone) === 1) {
            return $timezone;
        }

        return @timezone_open($timezone) !== false ? $timezone : null;
    }
}
