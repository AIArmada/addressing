<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use InvalidArgumentException;
use JsonException;

/**
 * The single supported interchange contract for offline reference comparison.
 */
final class ReferenceExportContract
{
    public const SCHEMA = 'aiarmada.addressing.reference-export';

    public const VERSION = 1;

    /** @var list<string> */
    public const TYPES = [
        'countries',
        'states',
        'cities',
        'areas',
        'area_names',
        'area_roles',
        'area_relationships',
        'area_state_links',
        'postal_codes',
        'area_postal_codes',
        'country_currency_links',
        'country_timezone_links',
    ];

    /** @param array<string, mixed> $value */
    public static function encode(array $value): string
    {
        try {
            return json_encode(
                self::canonicalize($value),
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION,
            );
        } catch (JsonException $exception) {
            throw new InvalidArgumentException('Reference export contains a value that cannot be encoded as JSON.', previous: $exception);
        }
    }

    /** @param array<string, mixed> $key */
    public static function sortKey(string $type, array $key): string
    {
        return $type . "\0" . self::encode($key);
    }

    /** @return array{country: string, code: string}|array{country: string, name: string} */
    public static function stateKey(string $country, ?string $code, string $name): array
    {
        return $code === null || $code === ''
            ? ['country' => $country, 'name' => $name]
            : ['country' => $country, 'code' => $code];
    }

    /** @param array<string, mixed> $key @param array<string, mixed> $data @return array<string, mixed> */
    public static function record(string $type, array $key, array $data): array
    {
        if (! in_array($type, self::TYPES, true)) {
            throw new InvalidArgumentException(sprintf('Unsupported reference record type [%s].', $type));
        }

        $body = ['type' => $type, 'key' => self::canonicalize($key), 'data' => self::canonicalize($data)];

        return ['kind' => 'record', ...$body, 'sha256' => hash('sha256', self::encode($body))];
    }

    /** @return array<string, mixed> */
    public static function decode(string $line, int $lineNumber): array
    {
        try {
            $value = json_decode($line, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException(sprintf('Invalid JSON on reference export line %d.', $lineNumber), previous: $exception);
        }

        if (! is_array($value)) {
            throw new InvalidArgumentException(sprintf('Reference export line %d must contain a JSON object.', $lineNumber));
        }

        return $value;
    }

    /** @param array<string, mixed> $value @return array<string, mixed> */
    public static function canonicalize(array $value): array
    {
        if (array_is_list($value)) {
            return array_map(static fn (mixed $item): mixed => is_array($item) ? self::canonicalize($item) : $item, $value);
        }

        ksort($value, SORT_STRING);

        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $value[$key] = self::canonicalize($item);
            }
        }

        return $value;
    }

    /** @param array<string, mixed> $header */
    public static function validateHeader(array $header): void
    {
        self::assertKeys($header, ['coverage', 'exported_at', 'kind', 'schema', 'scope', 'seed_revision', 'version'], 'header');

        if ($header['kind'] !== 'header' || $header['schema'] !== self::SCHEMA || $header['version'] !== self::VERSION || $header['coverage'] !== 'installed-observation-only') {
            throw new InvalidArgumentException('Reference export header schema or version is unsupported.');
        }

        if (! is_string($header['exported_at']) || ! is_array($header['scope']) || ($header['seed_revision'] !== null && ! is_string($header['seed_revision']))) {
            throw new InvalidArgumentException('Reference export header fields have invalid types.');
        }

        self::assertKeys($header['scope'], ['configured_providers', 'countries', 'seed_policy'], 'scope');
        $countries = $header['scope']['countries'];
        $providers = $header['scope']['configured_providers'];
        $seedPolicy = $header['scope']['seed_policy'];

        if (! is_array($countries) || ! array_is_list($countries) || ! is_array($providers) || ! array_is_list($providers) || ! is_array($seedPolicy)) {
            throw new InvalidArgumentException('Reference export scope lists are invalid.');
        }

        self::assertKeys($seedPolicy, ['full_city_countries', 'production_full_city_seed', 'postal_codes_enabled', 'indonesia_villages_enabled'], 'seed policy');

        if (! is_array($seedPolicy['full_city_countries']) || ! array_is_list($seedPolicy['full_city_countries']) || ! is_bool($seedPolicy['production_full_city_seed']) || ! is_bool($seedPolicy['postal_codes_enabled']) || ! is_bool($seedPolicy['indonesia_villages_enabled'])) {
            throw new InvalidArgumentException('Reference export seed policy has invalid types.');
        }

        $seedCountries = $seedPolicy['full_city_countries'];
        $sortedSeedCountries = $seedCountries;
        sort($sortedSeedCountries, SORT_STRING);

        if ($seedCountries !== $sortedSeedCountries || count($seedCountries) !== count(array_unique($seedCountries))) {
            throw new InvalidArgumentException('Reference export full-city seed countries must be unique and sorted.');
        }

        foreach ($seedCountries as $seedCountry) {
            if (! is_string($seedCountry) || preg_match('/^[A-Z]{2}$/', $seedCountry) !== 1) {
                throw new InvalidArgumentException('Reference export seed policy contains an invalid country code.');
            }
        }

        $sortedCountries = $countries;
        sort($sortedCountries, SORT_STRING);

        if ($countries !== $sortedCountries || count($countries) !== count(array_unique($countries))) {
            throw new InvalidArgumentException('Reference export countries must be unique and sorted.');
        }

        foreach ($countries as $country) {
            if (! is_string($country) || preg_match('/^[A-Z]{2}$/', $country) !== 1) {
                throw new InvalidArgumentException('Reference export country scope contains an invalid country code.');
            }
        }

        $previous = null;

        foreach ($providers as $provider) {
            if (! is_array($provider)) {
                throw new InvalidArgumentException('Reference export provider scope entry must be an object.');
            }

            self::assertKeys($provider, ['country', 'provider'], 'provider scope');

            if (! is_string($provider['country']) || ! is_string($provider['provider']) || ! in_array($provider['country'], $countries, true) || $provider['provider'] === '') {
                throw new InvalidArgumentException('Reference export provider scope entry is invalid.');
            }

            $current = [$provider['country'], $provider['provider']];

            if ($previous !== null && $previous >= $current) {
                throw new InvalidArgumentException('Reference export provider scopes must be unique and sorted.');
            }

            $previous = $current;
        }
    }

    /** @param array<string, mixed> $record */
    public static function validateRecord(array $record, int $lineNumber): void
    {
        self::assertKeys($record, ['data', 'key', 'kind', 'sha256', 'type'], sprintf('record on line %d', $lineNumber));

        if ($record['kind'] !== 'record' || ! is_string($record['type']) || ! in_array($record['type'], self::TYPES, true) || ! is_array($record['key']) || ! is_array($record['data']) || ! is_string($record['sha256'])) {
            throw new InvalidArgumentException(sprintf('Reference export record on line %d has invalid types.', $lineNumber));
        }

        $body = ['type' => $record['type'], 'key' => self::canonicalize($record['key']), 'data' => self::canonicalize($record['data'])];
        $expectedHash = hash('sha256', self::encode($body));

        if (! hash_equals($expectedHash, $record['sha256'])) {
            throw new InvalidArgumentException(sprintf('Reference export record checksum mismatch on line %d.', $lineNumber));
        }

        $expectedKey = self::keyFor($record['type'], $record['data']);

        if (self::encode($expectedKey) !== self::encode($record['key'])) {
            throw new InvalidArgumentException(sprintf('Reference export identity does not match data on line %d.', $lineNumber));
        }
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    private static function keyFor(string $type, array $data): array
    {
        $fields = match ($type) {
            'countries' => ['iso2', 'iso3', 'numeric_code', 'name', 'phone_code', 'native', 'capital', 'region', 'subregion', 'tld', 'latitude', 'longitude', 'emoji', 'emojiU', 'translations'],
            'states' => ['country', 'code', 'name', 'latitude', 'longitude'],
            'cities' => ['country', 'state', 'name', 'latitude', 'longitude'],
            'areas' => ['country', 'source', 'source_id', 'type', 'level', 'name', 'native_name', 'code', 'is_active', 'latitude', 'longitude', 'parent'],
            'area_names' => ['area', 'name', 'source', 'name_type', 'is_preferred'],
            'area_roles' => ['area', 'role', 'country', 'source', 'is_primary'],
            'area_relationships' => ['parent', 'child', 'relationship_type', 'hierarchy_type', 'source', 'valid_from', 'valid_until'],
            'area_state_links' => ['area', 'state', 'hierarchy_type'],
            'postal_codes' => ['country', 'code', 'is_active'],
            'area_postal_codes' => ['area', 'postal_code', 'source', 'source_id', 'relationship_type', 'is_primary'],
            'country_currency_links' => ['country', 'currency'],
            'country_timezone_links' => ['country', 'timezone'],
            default => throw new InvalidArgumentException(sprintf('Unsupported reference record type [%s].', $type)),
        };

        self::assertKeys($data, $fields, $type . ' data');

        switch ($type) {
            case 'countries':
                return ['country' => self::stringField($data, 'iso2')];
            case 'states':
                return self::stateKey(self::stringField($data, 'country'), self::nullableStringField($data, 'code'), self::stringField($data, 'name'));
            case 'cities':
                return ['country' => self::stringField($data, 'country'), 'state' => self::cityStateKey($data), 'name' => self::stringField($data, 'name')];
            case 'areas':
                return self::areaIdentity($data);
            case 'area_names':
                return ['area' => self::areaField($data, 'area'), 'name' => self::stringField($data, 'name'), 'name_type' => self::stringField($data, 'name_type'), 'source' => self::stringField($data, 'source')];
            case 'area_roles':
                return ['area' => self::areaField($data, 'area'), 'role' => self::stringField($data, 'role'), 'country' => self::stringField($data, 'country'), 'source' => self::stringField($data, 'source')];
            case 'area_relationships':
                return ['parent' => self::areaField($data, 'parent'), 'child' => self::areaField($data, 'child'), 'relationship_type' => self::stringField($data, 'relationship_type'), 'hierarchy_type' => self::hierarchyTypeField($data, 'hierarchy_type'), 'source' => self::stringField($data, 'source')];
            case 'area_state_links':
                return ['area' => self::areaField($data, 'area'), 'state' => self::stateField($data, 'state'), 'hierarchy_type' => self::hierarchyTypeField($data, 'hierarchy_type')];
            case 'postal_codes':
                return ['country' => self::stringField($data, 'country'), 'code' => self::stringField($data, 'code')];
            case 'area_postal_codes':
                return ['area' => self::areaField($data, 'area'), 'postal_code' => self::postalCodeField($data, 'postal_code'), 'relationship_type' => self::stringField($data, 'relationship_type'), 'source' => self::stringField($data, 'source')];
            case 'country_currency_links':
                return ['country' => self::stringField($data, 'country'), 'currency' => self::stringField($data, 'currency')];
            case 'country_timezone_links':
                return ['country' => self::stringField($data, 'country'), 'timezone' => self::stringField($data, 'timezone')];
            default:
                throw new InvalidArgumentException(sprintf('Unsupported reference record type [%s].', $type));
        }
    }

    /** @param array<string, mixed> $data @return array{country: string, source: string, source_id: string} */
    private static function areaIdentity(array $data): array
    {
        return [
            'country' => self::stringField($data, 'country'),
            'source' => self::stringField($data, 'source'),
            'source_id' => self::stringField($data, 'source_id'),
        ];
    }

    /** @param array<string, mixed> $data */
    private static function stringField(array $data, string $field): string
    {
        if (! is_string($data[$field] ?? null) || $data[$field] === '') {
            throw new InvalidArgumentException(sprintf('Reference data field [%s] must be a non-empty string.', $field));
        }

        return $data[$field];
    }

    /**
     * Hierarchy markers use the empty string for the default hierarchy
     * (see AddressAreaStateLink's null normalization), so only the type
     * is enforced here, not non-emptiness.
     *
     * @param  array<string, mixed>  $data
     */
    private static function hierarchyTypeField(array $data, string $field): string
    {
        if (! is_string($data[$field] ?? null)) {
            throw new InvalidArgumentException(sprintf('Reference data field [%s] must be a string.', $field));
        }

        return $data[$field];
    }

    /** @param array<string, mixed> $data */
    private static function nullableStringField(array $data, string $field): ?string
    {
        if ($data[$field] !== null && ! is_string($data[$field])) {
            throw new InvalidArgumentException(sprintf('Reference data field [%s] must be a string or null.', $field));
        }

        return $data[$field];
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    private static function arrayField(array $data, string $field): array
    {
        if (! is_array($data[$field] ?? null) || array_is_list($data[$field])) {
            throw new InvalidArgumentException(sprintf('Reference data field [%s] must be an object.', $field));
        }

        return $data[$field];
    }

    /** @param array<string, mixed> $data @return array{country: string, source: string, source_id: string} */
    private static function areaField(array $data, string $field): array
    {
        $area = self::arrayField($data, $field);
        self::assertKeys($area, ['country', 'source', 'source_id'], $field . ' area identity');

        return [
            'country' => self::stringField($area, 'country'),
            'source' => self::stringField($area, 'source'),
            'source_id' => self::stringField($area, 'source_id'),
        ];
    }

    /** @param array<string, mixed> $data @return array{country: string, code: string}|array{country: string, name: string} */
    private static function stateField(array $data, string $field): array
    {
        $state = self::arrayField($data, $field);
        self::assertKeys($state, ['country', array_key_exists('code', $state) ? 'code' : 'name'], $field . ' state identity');

        return [
            'country' => self::stringField($state, 'country'),
            (array_key_exists('code', $state) ? 'code' : 'name') => self::stringField($state, array_key_exists('code', $state) ? 'code' : 'name'),
        ];
    }

    /** @param array<string, mixed> $data @return array{code: string}|array{name: string}|null */
    private static function cityStateKey(array $data): ?array
    {
        if ($data['state'] === null) {
            return null;
        }

        if (! is_array($data['state']) || array_is_list($data['state'])) {
            throw new InvalidArgumentException('City state identity must be an object or null.');
        }

        if (array_key_exists('code', $data['state'])) {
            self::assertKeys($data['state'], ['code'], 'city state identity');

            return ['code' => self::stringField($data['state'], 'code')];
        }

        self::assertKeys($data['state'], ['name'], 'city state identity');

        return ['name' => self::stringField($data['state'], 'name')];
    }

    /** @param array<string, mixed> $data @return array{country: string, code: string} */
    private static function postalCodeField(array $data, string $field): array
    {
        $postalCode = self::arrayField($data, $field);
        self::assertKeys($postalCode, ['country', 'code'], $field . ' identity');

        return ['country' => self::stringField($postalCode, 'country'), 'code' => self::stringField($postalCode, 'code')];
    }

    /** @param array<string, mixed> $value @param list<string> $keys */
    private static function assertKeys(array $value, array $keys, string $label): void
    {
        $actual = array_keys($value);
        sort($actual, SORT_STRING);
        sort($keys, SORT_STRING);

        if ($actual !== $keys) {
            throw new InvalidArgumentException(sprintf('Reference export %s has an invalid field set.', $label));
        }
    }
}
