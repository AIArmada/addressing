<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Models\AddressCountry;
use Illuminate\Support\Str;

final class AddressCountryResolver
{
    public function resolve(mixed $country): ?AddressCountry
    {
        if ($country instanceof AddressCountry) {
            return $country;
        }

        if (! is_scalar($country)) {
            return null;
        }

        $value = mb_trim((string) $country);

        if ($value === '') {
            return null;
        }

        $query = AddressCountry::query();

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
        return $this->resolve($country)?->getKey();
    }

    public function timezoneFor(mixed $country): ?string
    {
        $resolved = $this->resolve($country);

        if (! $resolved instanceof AddressCountry) {
            return null;
        }

        $timezones = $resolved->timezones;

        if (is_array($timezones)) {
            $first = collect($timezones)->first();

            if (is_string($first)) {
                $timezone = $this->normalizeTimezone($first);

                if ($timezone !== null) {
                    return $timezone;
                }
            }
        }

        $metadata = $resolved->metadata;

        if (is_array($metadata) && is_string($metadata['timezone'] ?? null)) {
            return $this->normalizeTimezone($metadata['timezone']);
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
